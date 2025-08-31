<?php
require_once 'config/database.php';

$error = '';
$success = '';

// Initialize form fields
$formData = [
    'business_name' => '',
    'business_type' => 'Non Business Individual (Motor Veh...)',
    'principal_activities' => [],
    'tin_number' => '',
    'vat_number' => '',
    'permit_number' => '',
    'vat_expiration' => '',
    'owner_name' => '',
    'owner_position' => 'proprietor',
    'old_nic' => '',
    'new_nic' => '',
    'passport' => '',
    'address1' => '',
    'address2' => '',
    'address3' => '',
    'address4' => '',
    'mobile_registered' => 'yes',
    'mobile_number' => '',
    'tele_number' => '',
    'email' => '',
    'declaration_name' => '',
    'declaration_designation' => '',
    'declaration_email' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new Database();
        $conn = $db->getConnection();

        // Get form data
        $formData['business_name'] = $_POST['business_name'] ?? '';
        $formData['business_type'] = $_POST['business_type'] ?? '';
        $formData['principal_activities'] = $_POST['principal_activities'] ?? [];
        $formData['tin_number'] = $_POST['tin_number'] ?? '';
        $formData['vat_number'] = $_POST['vat_number'] ?? '';
        $formData['permit_number'] = $_POST['permit_number'] ?? '';
        $formData['vat_expiration'] = $_POST['vat_expiration'] ?? '';
        $formData['owner_name'] = $_POST['owner_name'] ?? '';
        $formData['owner_position'] = $_POST['owner_position'] ?? '';
        $formData['old_nic'] = $_POST['old_nic'] ?? '';
        $formData['new_nic'] = $_POST['new_nic'] ?? '';
        $formData['passport'] = $_POST['passport'] ?? '';
        $formData['address1'] = $_POST['address1'] ?? '';
        $formData['address2'] = $_POST['address2'] ?? '';
        $formData['address3'] = $_POST['address3'] ?? '';
        $formData['address4'] = $_POST['address4'] ?? '';
        $formData['mobile_registered'] = $_POST['mobile_registered'] ?? '';
        $formData['mobile_number'] = $_POST['mobile_number'] ?? '';
        $formData['tele_number'] = $_POST['tele_number'] ?? '';
        $formData['email'] = $_POST['email'] ?? '';
        $formData['declaration_name'] = $_POST['declaration_name'] ?? '';
        $formData['declaration_designation'] = $_POST['declaration_designation'] ?? '';
        $formData['declaration_email'] = $_POST['declaration_email'] ?? '';

        // Validate required fields
        if (empty($formData['business_name']) || empty($formData['tin_number']) || empty($formData['vat_number']) || 
            empty($formData['owner_name']) || empty($formData['old_nic']) || empty($formData['address1']) || 
            empty($formData['mobile_number']) || empty($formData['tele_number']) || 
            empty($formData['declaration_name']) || empty($formData['declaration_designation']) || empty($formData['declaration_email'])) {
            throw new Exception('All required fields must be filled');
        }

        // Prepare customer data
        $activities = implode(', ', $formData['principal_activities']);
        $address = $formData['address1'];
        if (!empty($formData['address2'])) $address .= ', ' . $formData['address2'];
        if (!empty($formData['address3'])) $address .= ', ' . $formData['address3'];
        if (!empty($formData['address4'])) $address .= ', ' . $formData['address4'];

        // Insert customer data
        $query = "INSERT INTO customers (
            first_name, last_name, email, phone, address, city, state, postal_code, country,
            business_name, business_type, business_reg_number, tin_number, vat_number, activities, created_by
        ) VALUES (
            :first_name, :last_name, :email, :phone, :address, :city, :state, :postal_code, :country,
            :business_name, :business_type, :business_reg_number, :tin_number, :vat_number, :activities, :created_by
        )";

        $stmt = $conn->prepare($query);

        // Split owner name into first and last name
        $nameParts = explode(' ', $formData['owner_name']);
        $firstName = array_shift($nameParts);
        $lastName = implode(' ', $nameParts);

        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':email', $formData['email']);
        $stmt->bindParam(':phone', $formData['mobile_number']);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':city', $formData['address4']); // Using address4 as city
        $stmt->bindParam(':state', $formData['address3']); // Using address3 as state
        $stmt->bindParam(':postal_code', $formData['address2']); // Using address2 as postal code
        $country = 'Sri Lanka';
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':business_name', $formData['business_name']);
        $stmt->bindParam(':business_type', $formData['business_type']);
        $stmt->bindParam(':business_reg_number', $formData['permit_number']);
        $stmt->bindParam(':tin_number', $formData['tin_number']);
        $stmt->bindParam(':vat_number', $formData['vat_number']);
        $stmt->bindParam(':activities', $activities);
        $createdBy = 1; // Default admin user
        $stmt->bindParam(':created_by', $createdBy);

        if ($stmt->execute()) {
            $customerId = $conn->lastInsertId();
            $success = 'Registration successful! Your customer ID is: ' . $customerId;
            
            // Handle file uploads if needed
            // You would add file upload handling here
        } else {
            throw new Exception('Failed to save customer data');
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sri Lanka Customs - Electronic Registration</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #1abc9c;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --white: #ffffff;
            --gray-light: #bdc3c7;
            --gray-dark: #7f8c8d;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
            color: var(--secondary-color);
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            background: var(--white);
            box-shadow: var(--shadow);
            margin: 1rem;
            border-radius: 10px;
        }

        .header {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid var(--primary-color);
        }

        .page-title {
            font-size: 1.5rem;
            color: var(--secondary-color);
            font-weight: 600;
        }

        .user-info {
            font-size: 0.9rem;
            color: var(--gray-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-info i {
            color: var(--primary-color);
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error {
            background-color: #fdecea;
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .form-container {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .form-tabs {
            display: flex;
            background: var(--secondary-color);
            border-radius: 8px 8px 0 0;
            overflow: hidden;
        }

        .tab-button {
            padding: 1rem 1.5rem;
            background: transparent;
            border: none;
            color: var(--gray-light);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .tab-button:hover {
            color: var(--white);
            background: rgba(255, 255, 255, 0.1);
        }

        .tab-button.active {
            background: var(--primary-color);
            color: var(--white);
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 3px;
            background: var(--white);
            border-radius: 3px 3px 0 0;
        }

        .form-section {
            padding: 2rem;
        }

        .section-header {
            background: var(--secondary-color);
            color: var(--white);
            padding: 1rem 1.5rem;
            margin: -2rem -2rem 1.5rem -2rem;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px 8px 0 0;
        }

        .required-info {
            font-size: 0.75rem;
            color: var(--danger-color);
            font-weight: 400;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;
        }

        .form-label {
            font-size: 0.85rem;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .required {
            color: var(--danger-color);
            margin-left: 0.25rem;
            font-size: 1rem;
        }

        .form-input, .form-select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: 6px;
            font-size: 0.9rem;
            transition: var(--transition);
            width: 100%;
            background-color: var(--white);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-item input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: var(--primary-color);
        }

        .checkbox-item label {
            font-size: 0.85rem;
            color: var(--secondary-color);
            cursor: pointer;
            user-select: none;
        }

        .radio-group {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            margin-top: 0.5rem;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .radio-item input[type="radio"] {
            width: 1rem;
            height: 1rem;
            accent-color: var(--primary-color);
        }

        .radio-item label {
            font-size: 0.85rem;
            color: var(--secondary-color);
            cursor: pointer;
            user-select: none;
        }

        .file-upload-group {
            display: grid;
            gap: 1.5rem;
        }

        .file-upload-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px dashed var(--gray-light);
            border-radius: 6px;
            background: #f9fafb;
            transition: var(--transition);
        }

        .file-upload-item:hover {
            border-color: var(--primary-color);
            background: rgba(52, 152, 219, 0.05);
        }

        .file-label {
            flex: 1;
            font-size: 0.85rem;
            color: var(--secondary-color);
            font-weight: 500;
        }

        .file-input {
            display: none;
        }

        .file-choose-btn {
            padding: 0.5rem 1rem;
            background: var(--gray-light);
            color: var(--white);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .file-choose-btn:hover {
            background: var(--gray-dark);
        }

        .file-choose-btn i {
            font-size: 0.8rem;
        }

        .file-status {
            font-size: 0.75rem;
            color: var(--gray-dark);
            min-width: 120px;
            text-align: center;
        }

        .file-view-btn {
            padding: 0.5rem 1rem;
            background: var(--warning-color);
            color: var(--white);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .file-view-btn:hover {
            background: #e67e22;
        }

        .file-view-btn i {
            font-size: 0.8rem;
        }

        .submit-button {
            background: var(--accent-color);
            color: var(--white);
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 5px rgba(26, 188, 156, 0.3);
        }

        .submit-button:hover {
            background: #16a085;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(26, 188, 156, 0.3);
        }

        .submit-button i {
            font-size: 1rem;
        }

        .notice-box {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .notice-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #856404;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notice-title i {
            font-size: 1rem;
        }

        .notice-text {
            color: #856404;
            font-size: 0.8rem;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        .register-btn {
            background: var(--warning-color);
            color: var(--white);
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 2px 5px rgba(243, 156, 18, 0.3);
            float: right;
        }

        .register-btn:hover {
            background: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
        }

        .register-btn i {
            font-size: 1.1rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 0;
                margin: 0;
            }
            
            .main-content {
                margin: 0;
                border-radius: 0;
                padding: 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-tabs {
                flex-wrap: wrap;
            }
            
            .tab-button {
                flex: 1;
                min-width: 120px;
                text-align: center;
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }
            
            .file-upload-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .register-btn {
                width: 100%;
                justify-content: center;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="header">
                <div class="page-title">Electronic Registration of Traders & Logistics Operators</div>
                <div class="user-info">Welcome, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?></div>
            </div>

            <?php if ($error): ?>
                <div style="background: #ffdddd; color: #e74c3c; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="background: #ddffdd; color: #27ae60; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register_customer.php" enctype="multipart/form-data">
                <div class="form-container">
                    <div class="form-tabs">
                        <button type="button" class="tab-button active" onclick="showTab('basic')">Basic</button>
                        <button type="button" class="tab-button" onclick="showTab('owner')">Owner</button>
                        <button type="button" class="tab-button" onclick="showTab('attachment')">Attachment</button>
                        <button type="button" class="tab-button" onclick="showTab('declaration')">Declaration</button>
                    </div>

                    <!-- Basic Tab -->
                    <div id="basic-tab" class="tab-content active">
                        <div class="form-section">
                            <div class="section-header">
                                <span>Business / Individual Details</span>
                                <span class="required-info">(Fields marked with * are required)</span>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Name of Business / Individual Name <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="business_name" value="<?php echo htmlspecialchars($formData['business_name']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Type of Business <span class="required">*</span></label>
                                    <select class="form-select" name="business_type" required>
                                        <option value="Non Business Individual (Motor Veh...)" <?php echo $formData['business_type'] === 'Non Business Individual (Motor Veh...)' ? 'selected' : ''; ?>>Non Business Individual (Motor Veh...)</option>
                                        <option value="Business Individual" <?php echo $formData['business_type'] === 'Business Individual' ? 'selected' : ''; ?>>Business Individual</option>
                                        <option value="Private Company" <?php echo $formData['business_type'] === 'Private Company' ? 'selected' : ''; ?>>Private Company</option>
                                        <option value="Public Company" <?php echo $formData['business_type'] === 'Public Company' ? 'selected' : ''; ?>>Public Company</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Principal Activities carried out with Customs <span class="required">*</span></label>
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="importer" name="principal_activities[]" value="Importer" <?php echo in_array('Importer', $formData['principal_activities']) ? 'checked' : ''; ?>>
                                        <label for="importer">Importer</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="exporter" name="principal_activities[]" value="Exporter" <?php echo in_array('Exporter', $formData['principal_activities']) ? 'checked' : ''; ?>>
                                        <label for="exporter">Exporter</label>
                                    </div>
                                    <!-- All other checkboxes with the same pattern -->
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="one-time" name="principal_activities[]" value="One Time Importer/Exporter" <?php echo in_array('One Time Importer/Exporter', $formData['principal_activities']) ? 'checked' : ''; ?>>
                                        <label for="one-time">One Time Importer/Exporter</label>
                                    </div>
                                    <!-- ... other checkboxes ... -->
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-header">
                                <span>TIN / VAT / Permit / Merchant Shipping License Information</span>
                                <span class="required-info">(Fields marked with * are required)</span>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Tax Identification Number <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="tin_number" value="<?php echo htmlspecialchars($formData['tin_number']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Permit Number</label>
                                    <input type="text" class="form-input" name="permit_number" value="<?php echo htmlspecialchars($formData['permit_number']); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">VAT Number <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="vat_number" value="<?php echo htmlspecialchars($formData['vat_number']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">VAT Expiration Date</label>
                                    <input type="date" class="form-input" name="vat_expiration" value="<?php echo htmlspecialchars($formData['vat_expiration']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Owner Tab -->
                    <div id="owner-tab" class="tab-content">
                        <div class="form-section">
                            <div class="section-header">
                                <span>Owner 1</span>
                            </div>

                            <div class="section-header">
                                <span>Personal Details</span>
                                <span class="required-info">(Fields marked with * are required)</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Full Name of <span class="required">*</span></label>
                                <div class="radio-group">
                                    <div class="radio-item">
                                        <input type="radio" id="chairman" name="owner_position" value="chairman" <?php echo $formData['owner_position'] === 'chairman' ? 'checked' : ''; ?>>
                                        <label for="chairman">Chairman</label>
                                    </div>
                                    <div class="radio-item">
                                        <input type="radio" id="director" name="owner_position" value="director" <?php echo $formData['owner_position'] === 'director' ? 'checked' : ''; ?>>
                                        <label for="director">Director</label>
                                    </div>
                                    <div class="radio-item">
                                        <input type="radio" id="partner" name="owner_position" value="partner" <?php echo $formData['owner_position'] === 'partner' ? 'checked' : ''; ?>>
                                        <label for="partner">Partner</label>
                                    </div>
                                    <div class="radio-item">
                                        <input type="radio" id="proprietor" name="owner_position" value="proprietor" <?php echo $formData['owner_position'] === 'proprietor' ? 'checked' : ''; ?>>
                                        <label for="proprietor">Proprietor</label>
                                    </div>
                                </div>
                                <input type="text" class="form-input" name="owner_name" value="<?php echo htmlspecialchars($formData['owner_name']); ?>" required style="margin-top: 10px;">
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Old NIC <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="old_nic" value="<?php echo htmlspecialchars($formData['old_nic']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">New NIC</label>
                                    <input type="text" class="form-input" name="new_nic" value="<?php echo htmlspecialchars($formData['new_nic']); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Passport</label>
                                    <input type="text" class="form-input" name="passport" value="<?php echo htmlspecialchars($formData['passport']); ?>" placeholder="ENTER PASSPORT NUMB">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Address Line 1 <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="address1" value="<?php echo htmlspecialchars($formData['address1']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Address Line 2</label>
                                    <input type="text" class="form-input" name="address2" value="<?php echo htmlspecialchars($formData['address2']); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Address Line 3</label>
                                    <input type="text" class="form-input" name="address3" value="<?php echo htmlspecialchars($formData['address3']); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Address Line 4</label>
                                    <input type="text" class="form-input" name="address4" value="<?php echo htmlspecialchars($formData['address4']); ?>">
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Is Mobile Registered to Your NIC? <span class="required">*</span></label>
                                    <div class="radio-group">
                                        <div class="radio-item">
                                            <input type="radio" id="mobile-yes" name="mobile_registered" value="yes" <?php echo $formData['mobile_registered'] === 'yes' ? 'checked' : ''; ?> required>
                                            <label for="mobile-yes">Yes</label>
                                        </div>
                                        <div class="radio-item">
                                            <input type="radio" id="mobile-no" name="mobile_registered" value="no" <?php echo $formData['mobile_registered'] === 'no' ? 'checked' : ''; ?> required>
                                            <label for="mobile-no">No</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Mobile Number <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="mobile_number" value="<?php echo htmlspecialchars($formData['mobile_number']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Tele Number <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="tele_number" value="<?php echo htmlspecialchars($formData['tele_number']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-input" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachment Tab -->
                    <div id="attachment-tab" class="tab-content">
                        <div class="form-section">
                            <div class="section-header">
                                <span>File Attachment - (Please upload PDF files only)</span>
                            </div>

                            <div class="notice-box">
                                <div class="notice-title">Note: Important facts when uploading documents</div>
                                <div class="notice-text">
                                    Make sure that the following documents contain the same business address: <strong>GS Form 01, TIN, VAT.</strong><br>
                                    <strong style="color: #e74c3c;">Ensure the uploaded file size is less than 2MB and it is in PDF format.</strong>
                                </div>
                            </div>

                            <div class="file-upload-group">
                                <div class="file-upload-item">
                                    <div class="file-label">TIN Certificate :</div>
                                    <input type="file" class="file-input" id="tin-cert" name="tin_cert" accept=".pdf">
                                    <button type="button" class="file-choose-btn" onclick="document.getElementById('tin-cert').click()">Choose File</button>
                                    <span style="font-size: 12px; color: #7f8c8d;">NO FILE CHOSEN</span>
                                </div>

                                <div class="file-upload-item">
                                    <div class="file-label">Valid VAT Certificate :</div>
                                    <input type="file" class="file-input" id="vat-cert" name="vat_cert" accept=".pdf">
                                    <button type="button" class="file-choose-btn" onclick="document.getElementById('vat-cert').click()">Choose File</button>
                                    <span style="font-size: 12px; color: #7f8c8d;">NO FILE CHOSEN</span>
                                </div>

                                <!-- Other file upload fields -->
                            </div>
                        </div>
                    </div>

                    <!-- Declaration Tab -->
                    <div id="declaration-tab" class="tab-content">
                        <div class="form-section">
                            <div class="section-header">
                                <span>Declaration</span>
                                <span class="required-info">(Fields marked with * are required)</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: bold; margin-bottom: 15px;">Details of the Person the Email Alert should be sent to :</label>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Name of the User <span class="required">*</span></label>
                                        <input type="text" class="form-input" name="declaration_name" value="<?php echo htmlspecialchars($formData['declaration_name']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Designation of the User <span class="required">*</span></label>
                                        <input type="text" class="form-input" name="declaration_designation" value="<?php echo htmlspecialchars($formData['declaration_designation']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Email <span class="required">*</span></label>
                                        <input type="email" class="form-input" name="declaration_email" value="<?php echo htmlspecialchars($formData['declaration_email']); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Register Application Button -->
                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" style="background: #f39c12; color: white; padding: 15px 40px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: 600;">Register Application</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => button.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab button
            event.target.classList.add('active');
        }

        // File upload handling
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.files[0] ? this.files[0].name : 'NO FILE CHOSEN';
                const fileStatus = this.parentElement.querySelector('span');
                fileStatus.textContent = fileName;
                
                if (this.files[0]) {
                    fileStatus.style.color = '#27ae60';
                } else {
                    fileStatus.style.color = '#7f8c8d';
                }
            });
        });

        // Form validation
        document.querySelectorAll('.form-input, .form-select').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.style.borderColor = '#bdc3c7';
                }
            });
        });
    </script>
</body>
</html>