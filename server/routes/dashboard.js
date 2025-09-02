import express from 'express'
import { pool } from '../config/database.js'
import { authenticateToken, requireEmployeeOrAdmin } from '../middleware/auth.js'

const router = express.Router()

// Apply authentication to all routes
router.use(authenticateToken)
router.use(requireEmployeeOrAdmin)

// Get dashboard statistics
router.get('/stats', async (req, res, next) => {
  try {
    const userId = req.user.id
    const userRole = req.user.role

    // Build conditions based on user role
    let customerCondition = ''
    let customerParams = []
    
    if (userRole === 'employee') {
      customerCondition = 'WHERE created_by = ?'
      customerParams = [userId]
    }

    // Get total customers
    const [customerCount] = await pool.execute(
      `SELECT COUNT(*) as total FROM customers ${customerCondition}`,
      customerParams
    )

    // Get total employees (admin only)
    let employeeCount = 0
    if (userRole === 'admin') {
      const [empCount] = await pool.execute(
        'SELECT COUNT(*) as total FROM users WHERE role_id = 2'
      )
      employeeCount = empCount[0].total
    }

    // Mock data for other stats (in a real app, these would come from actual data)
    const pendingTasks = 18
    const monthlyRevenue = 24580

    res.json({
      totalCustomers: customerCount[0].total,
      totalEmployees: employeeCount,
      pendingTasks,
      monthlyRevenue
    })
  } catch (error) {
    next(error)
  }
})

// Get recent customers
router.get('/recent-customers', async (req, res, next) => {
  try {
    const userId = req.user.id
    const userRole = req.user.role

    // Build conditions based on user role
    let condition = ''
    let params = []
    
    if (userRole === 'employee') {
      condition = 'WHERE c.created_by = ?'
      params = [userId]
    }

    const [customers] = await pool.execute(`
      SELECT c.id, c.first_name, c.last_name, c.email, c.phone, c.created_at
      FROM customers c
      ${condition}
      ORDER BY c.created_at DESC
      LIMIT 5
    `, params)

    res.json(customers)
  } catch (error) {
    next(error)
  }
})

export { router as dashboardRoutes }
