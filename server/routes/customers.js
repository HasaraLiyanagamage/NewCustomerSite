import express from 'express'
import { body, validationResult, query } from 'express-validator'
import { pool } from '../config/database.js'
import { authenticateToken, requireEmployeeOrAdmin } from '../middleware/auth.js'

const router = express.Router()

// Apply authentication to all routes
router.use(authenticateToken)
router.use(requireEmployeeOrAdmin)

// Get all customers with pagination and search
router.get('/', [
  query('page').optional().isInt({ min: 1 }).withMessage('Page must be a positive integer'),
  query('limit').optional().isInt({ min: 1, max: 100 }).withMessage('Limit must be between 1 and 100'),
  query('search').optional().isString().withMessage('Search must be a string')
], async (req, res, next) => {
  try {
    const errors = validationResult(req)
    if (!errors.isEmpty()) {
      return res.status(400).json({ 
        message: 'Validation failed',
        errors: errors.array()
      })
    }

    const page = parseInt(req.query.page) || 1
    const limit = parseInt(req.query.limit) || 10
    const search = req.query.search || ''
    const offset = (page - 1) * limit

    // Build search condition
    let searchCondition = ''
    let searchParams = []
    
    if (search) {
      searchCondition = `WHERE (c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)`
      const searchPattern = `%${search}%`
      searchParams = [searchPattern, searchPattern, searchPattern, searchPattern]
    }

    // Add role-based filtering for employees
    if (req.user.role === 'employee') {
      if (searchCondition) {
        searchCondition += ' AND c.created_by = ?'
      } else {
        searchCondition = 'WHERE c.created_by = ?'
      }
      searchParams.push(req.user.id)
    }

    // Get total count
    const countQuery = `SELECT COUNT(*) as total FROM customers c ${searchCondition}`
    const [countRows] = await pool.execute(countQuery, searchParams)
    const total = countRows[0].total

    // Get customers
    const customersQuery = `
      SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name 
      FROM customers c 
      LEFT JOIN users u ON c.created_by = u.id 
      ${searchCondition}
      ORDER BY c.created_at DESC 
      LIMIT ? OFFSET ?
    `
    
    const [customers] = await pool.execute(customersQuery, [...searchParams, limit, offset])

    res.json({
      customers,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit)
      }
    })
  } catch (error) {
    next(error)
  }
})

// Get customer by ID
router.get('/:id', async (req, res, next) => {
  try {
    const { id } = req.params

    // Build query with role-based access
    let query = `
      SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name 
      FROM customers c 
      LEFT JOIN users u ON c.created_by = u.id 
      WHERE c.id = ?
    `
    let params = [id]

    // Add role-based filtering for employees
    if (req.user.role === 'employee') {
      query += ' AND c.created_by = ?'
      params.push(req.user.id)
    }

    const [rows] = await pool.execute(query, params)

    if (rows.length === 0) {
      return res.status(404).json({ message: 'Customer not found' })
    }

    res.json(rows[0])
  } catch (error) {
    next(error)
  }
})

// Create new customer
router.post('/', [
  body('firstName').notEmpty().withMessage('First name is required'),
  body('lastName').notEmpty().withMessage('Last name is required'),
  body('email').isEmail().withMessage('Valid email is required'),
  body('phone').notEmpty().withMessage('Phone number is required'),
  body('businessName').notEmpty().withMessage('Business name is required'),
  body('businessType').notEmpty().withMessage('Business type is required'),
  body('tinNumber').notEmpty().withMessage('TIN number is required'),
  body('vatNumber').notEmpty().withMessage('VAT number is required')
], async (req, res, next) => {
  try {
    const errors = validationResult(req)
    if (!errors.isEmpty()) {
      return res.status(400).json({ 
        message: 'Validation failed',
        errors: errors.array()
      })
    }

    const {
      firstName,
      lastName,
      email,
      phone,
      address,
      city,
      state,
      postalCode,
      country,
      businessName,
      businessType,
      businessRegNumber,
      tinNumber,
      vatNumber,
      activities
    } = req.body

    // Check if email already exists
    const [existingCustomer] = await pool.execute(
      'SELECT id FROM customers WHERE email = ?',
      [email]
    )

    if (existingCustomer.length > 0) {
      return res.status(400).json({ message: 'Customer with this email already exists' })
    }

    // Insert customer
    const [result] = await pool.execute(`
      INSERT INTO customers (
        first_name, last_name, email, phone, address, city, state, postal_code, country,
        business_name, business_type, business_reg_number, tin_number, vat_number, activities, created_by
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `, [
      firstName, lastName, email, phone, address, city, state, postalCode, country,
      businessName, businessType, businessRegNumber, tinNumber, vatNumber, activities, req.user.id
    ])

    res.status(201).json({
      message: 'Customer created successfully',
      customerId: result.insertId
    })
  } catch (error) {
    next(error)
  }
})

// Update customer
router.put('/:id', [
  body('firstName').notEmpty().withMessage('First name is required'),
  body('lastName').notEmpty().withMessage('Last name is required'),
  body('email').isEmail().withMessage('Valid email is required'),
  body('phone').notEmpty().withMessage('Phone number is required'),
  body('businessName').notEmpty().withMessage('Business name is required'),
  body('businessType').notEmpty().withMessage('Business type is required'),
  body('tinNumber').notEmpty().withMessage('TIN number is required'),
  body('vatNumber').notEmpty().withMessage('VAT number is required')
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
    const {
      firstName,
      lastName,
      email,
      phone,
      address,
      city,
      state,
      postalCode,
      country,
      businessName,
      businessType,
      businessRegNumber,
      tinNumber,
      vatNumber,
      activities
    } = req.body

    // Check if customer exists and user has permission
    let checkQuery = 'SELECT id, created_by FROM customers WHERE id = ?'
    let checkParams = [id]

    if (req.user.role === 'employee') {
      checkQuery += ' AND created_by = ?'
      checkParams.push(req.user.id)
    }

    const [existingCustomer] = await pool.execute(checkQuery, checkParams)

    if (existingCustomer.length === 0) {
      return res.status(404).json({ message: 'Customer not found or access denied' })
    }

    // Check if email already exists for another customer
    const [emailCheck] = await pool.execute(
      'SELECT id FROM customers WHERE email = ? AND id != ?',
      [email, id]
    )

    if (emailCheck.length > 0) {
      return res.status(400).json({ message: 'Customer with this email already exists' })
    }

    // Update customer
    await pool.execute(`
      UPDATE customers SET 
        first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, 
        postal_code = ?, country = ?, business_name = ?, business_type = ?, business_reg_number = ?, 
        tin_number = ?, vat_number = ?, activities = ?, updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    `, [
      firstName, lastName, email, phone, address, city, state, postalCode, country,
      businessName, businessType, businessRegNumber, tinNumber, vatNumber, activities, id
    ])

    res.json({ message: 'Customer updated successfully' })
  } catch (error) {
    next(error)
  }
})

// Delete customer
router.delete('/:id', async (req, res, next) => {
  try {
    const { id } = req.params

    // Check if customer exists and user has permission
    let checkQuery = 'SELECT id, created_by FROM customers WHERE id = ?'
    let checkParams = [id]

    if (req.user.role === 'employee') {
      checkQuery += ' AND created_by = ?'
      checkParams.push(req.user.id)
    }

    const [existingCustomer] = await pool.execute(checkQuery, checkParams)

    if (existingCustomer.length === 0) {
      return res.status(404).json({ message: 'Customer not found or access denied' })
    }

    // Delete customer
    await pool.execute('DELETE FROM customers WHERE id = ?', [id])

    res.json({ message: 'Customer deleted successfully' })
  } catch (error) {
    next(error)
  }
})

export { router as customerRoutes }
