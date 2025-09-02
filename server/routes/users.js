import express from 'express'
import bcrypt from 'bcryptjs'
import { body, validationResult } from 'express-validator'
import { pool } from '../config/database.js'
import { authenticateToken, requireAdmin } from '../middleware/auth.js'

const router = express.Router()

// Apply authentication to all routes
router.use(authenticateToken)

// Get all users (admin only)
router.get('/', requireAdmin, async (req, res, next) => {
  try {
    const [users] = await pool.execute(`
      SELECT u.*, r.name as role_name 
      FROM users u 
      JOIN roles r ON u.role_id = r.id 
      WHERE u.role_id = 2
      ORDER BY u.created_at DESC
    `)

    res.json(users)
  } catch (error) {
    next(error)
  }
})

// Get user by ID
router.get('/:id', async (req, res, next) => {
  try {
    const { id } = req.params

    // Users can only view their own profile unless they're admin
    if (req.user.role !== 'admin' && req.user.id !== parseInt(id)) {
      return res.status(403).json({ message: 'Access denied' })
    }

    const [rows] = await pool.execute(
      `SELECT u.*, r.name as role_name 
       FROM users u 
       JOIN roles r ON u.role_id = r.id 
       WHERE u.id = ?`,
      [id]
    )

    if (rows.length === 0) {
      return res.status(404).json({ message: 'User not found' })
    }

    const user = rows[0]
    res.json({
      id: user.id,
      username: user.username,
      email: user.email,
      firstName: user.first_name,
      lastName: user.last_name,
      role: user.role_name,
      createdAt: user.created_at,
      updatedAt: user.updated_at
    })
  } catch (error) {
    next(error)
  }
})

// Create new user (admin only)
router.post('/', requireAdmin, [
  body('username').notEmpty().withMessage('Username is required'),
  body('email').isEmail().withMessage('Valid email is required'),
  body('password').isLength({ min: 6 }).withMessage('Password must be at least 6 characters'),
  body('firstName').notEmpty().withMessage('First name is required'),
  body('lastName').notEmpty().withMessage('Last name is required')
], async (req, res, next) => {
  try {
    const errors = validationResult(req)
    if (!errors.isEmpty()) {
      return res.status(400).json({ 
        message: 'Validation failed',
        errors: errors.array()
      })
    }

    const { username, email, password, firstName, lastName } = req.body

    // Check if username already exists
    const [existingUser] = await pool.execute(
      'SELECT id FROM users WHERE username = ? OR email = ?',
      [username, email]
    )

    if (existingUser.length > 0) {
      return res.status(400).json({ message: 'Username or email already exists' })
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 10)

    // Insert user (role_id = 2 for employee)
    const [result] = await pool.execute(`
      INSERT INTO users (username, email, password_hash, first_name, last_name, role_id)
      VALUES (?, ?, ?, ?, ?, 2)
    `, [username, email, hashedPassword, firstName, lastName])

    res.status(201).json({
      message: 'User created successfully',
      userId: result.insertId
    })
  } catch (error) {
    next(error)
  }
})

// Update user
router.put('/:id', [
  body('firstName').notEmpty().withMessage('First name is required'),
  body('lastName').notEmpty().withMessage('Last name is required'),
  body('email').isEmail().withMessage('Valid email is required')
], async (req, res, next) => {
  try {
    const errors = validationResult(req)
    if (!errors.isEmpty()) {
      return res.status(400).json({ 
        message: 'Validation failed',
        errors: errors.array()
      })
    }

    const { id } = req.params
    const { firstName, lastName, email, currentPassword, newPassword } = req.body

    // Users can only update their own profile unless they're admin
    if (req.user.role !== 'admin' && req.user.id !== parseInt(id)) {
      return res.status(403).json({ message: 'Access denied' })
    }

    // Check if user exists
    const [existingUser] = await pool.execute(
      'SELECT * FROM users WHERE id = ?',
      [id]
    )

    if (existingUser.length === 0) {
      return res.status(404).json({ message: 'User not found' })
    }

    const user = existingUser[0]

    // Check if email already exists for another user
    const [emailCheck] = await pool.execute(
      'SELECT id FROM users WHERE email = ? AND id != ?',
      [email, id]
    )

    if (emailCheck.length > 0) {
      return res.status(400).json({ message: 'Email already exists' })
    }

    // Handle password change if provided
    if (newPassword) {
      if (!currentPassword) {
        return res.status(400).json({ message: 'Current password is required' })
      }

      const isValidPassword = await bcrypt.compare(currentPassword, user.password_hash)
      if (!isValidPassword) {
        return res.status(400).json({ message: 'Current password is incorrect' })
      }

      const hashedPassword = await bcrypt.hash(newPassword, 10)
      
      await pool.execute(`
        UPDATE users SET 
          first_name = ?, last_name = ?, email = ?, password_hash = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
      `, [firstName, lastName, email, hashedPassword, id])
    } else {
      await pool.execute(`
        UPDATE users SET 
          first_name = ?, last_name = ?, email = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
      `, [firstName, lastName, email, id])
    }

    res.json({ message: 'User updated successfully' })
  } catch (error) {
    next(error)
  }
})

// Delete user (admin only)
router.delete('/:id', requireAdmin, async (req, res, next) => {
  try {
    const { id } = req.params

    // Prevent deleting self
    if (req.user.id === parseInt(id)) {
      return res.status(400).json({ message: 'Cannot delete your own account' })
    }

    // Check if user exists and is an employee
    const [existingUser] = await pool.execute(
      'SELECT id FROM users WHERE id = ? AND role_id = 2',
      [id]
    )

    if (existingUser.length === 0) {
      return res.status(404).json({ message: 'User not found' })
    }

    await pool.execute('DELETE FROM users WHERE id = ?', [id])

    res.json({ message: 'User deleted successfully' })
  } catch (error) {
    next(error)
  }
})

export { router as userRoutes }
