import jwt from 'jsonwebtoken'
import { pool } from '../config/database.js'

export const authenticateToken = async (req, res, next) => {
  const authHeader = req.headers['authorization']
  const token = authHeader && authHeader.split(' ')[1]

  if (!token) {
    return res.status(401).json({ message: 'Access token required' })
  }

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET || 'your-secret-key')
    
    // Get user from database
    const [rows] = await pool.execute(
      'SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?',
      [decoded.userId]
    )

    if (rows.length === 0) {
      return res.status(401).json({ message: 'Invalid token' })
    }

    req.user = {
      id: rows[0].id,
      username: rows[0].username,
      email: rows[0].email,
      firstName: rows[0].first_name,
      lastName: rows[0].last_name,
      role: rows[0].role_name
    }

    next()
  } catch (error) {
    return res.status(403).json({ message: 'Invalid or expired token' })
  }
}

export const requireAdmin = (req, res, next) => {
  if (req.user.role !== 'admin') {
    return res.status(403).json({ message: 'Admin access required' })
  }
  next()
}

export const requireEmployeeOrAdmin = (req, res, next) => {
  if (!['admin', 'employee'].includes(req.user.role)) {
    return res.status(403).json({ message: 'Employee or admin access required' })
  }
  next()
}
