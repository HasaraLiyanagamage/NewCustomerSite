<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sri Lanka Customs - Electronic Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            padding: 0 20px 30px;
            border-bottom: 1px solid #34495e;
        }

        .logo img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
            background: #3498db;
            border-radius: 4px;
        }

        .logo-text {
            font-weight: bold;
            font-size: 14px;
        }

        .nav-section {
            margin: 20px 0;
        }

        .nav-title {
            padding: 10px 20px;
            font-size: 12px;
            color: #bdc3c7;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-item {
            display: block;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            font-size: 13px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .nav-item:hover, .nav-item.active {
            background: #34495e;
            border-left-color: #3498db;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background: #ecf0f1;
        }

        .header {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 18px;
            color: #2c3e50;
            font-weight: 600;
        }

        .user-info {
            font-size: 14px;
            color: #7f8c8d;
        }
        </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <div style="width: 30px; height: 30px; background: #3498db; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">🇱🇰</div>
                <div class="logo-text">Sri Lanka Customs<br><small>ICT Directorate</small></div>
            </div>
            
            <div class="nav-section">
                <div class="nav-title">Navigation</div>
                <a href="#" class="nav-item active">📄 Application</a>
                <a href="#" class="nav-item">📥 Downloads</a>
                <a href="#" class="nav-item">📋 GS Form 01</a>
                <a href="#" class="nav-item">📋 GS Form 02</a>
                <a href="#" class="nav-item">❓ What Should I Fill/Upload (Mandatory Fields)</a>
            </div>
            
            <div class="nav-section">
                <div class="nav-title">📚 Manuals</div>
                <a href="#" class="nav-item">Video Tutorial - Sinhala</a>
                <a href="#" class="nav-item">Video Tutorial - Tamil</a>
                <a href="#" class="nav-item">Video Tutorial - English</a>
                <a href="#" class="nav-item">Error-Free Submission</a>
                <a href="#" class="nav-item">Frequently Asked Questions</a>
            </div>
            
            <div class="nav-section">
                <a href="#" class="nav-item">📞 Contact Us</a>
                <a href="#" class="nav-item">👤 Profile</a>
            </div>
        </div>

        <div class="main-content">
            <div class="header">
                <div class="page-title">Electronic Registration of Traders & Logistics Operators</div>
                <div class="user-info">Welcome, A H M JINADASA</div>
            </div>

            <div class="form-container">
                <div class="form-tabs">
                    <button class="tab-button active" onclick="showTab('basic')">Basic</button>
                    <button class="tab-button" onclick="showTab('owner')">Owner</button>
                    <button class="tab-button" onclick="showTab('attachment')">Attachment</button>
                    <button class="tab-button" onclick="showTab('declaration')">Declaration</button>
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
                                <input type="text" class="form-input" value="A H M JINADASA">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Type of Business <span class="required">*</span></label>
                                <select class="form-select">
                                    <option>Non Business Individual (Motor Veh...)</option>
                                    <option>Business Individual</option>
                                    <option>Private Company</option>
                                    <option>Public Company</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">If Other, Please Specify</label>
                            <input type="text" class="form-input">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Principal Activities carried out with Customs <span class="required">*</span></label>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="importer">
                                    <label for="importer">Importer</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="exporter">
                                    <label for="exporter">Exporter</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="clearing-agent">
                                    <label for="clearing-agent">Cargo Clearing Agent (CHA)</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="courier">
                                    <label for="courier">Courier Service</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="vessel-agent">
                                    <label for="vessel-agent">Vessel Agent</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="upr-agent">
                                    <label for="upr-agent">UPR Clearing Agent</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="door-service">
                                    <label for="door-service">Door to Door Service</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="freight">
                                    <label for="freight">Freight Forwarder/NVOCC</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="eoi">
                                    <label for="eoi">EOI</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="permit">
                                    <label for="permit">Permit</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="one-time" checked>
                                    <label for="one-time">One Time Importer/Exporter</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="transporter">
                                    <label for="transporter">Transporter</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="bank">
                                    <label for="bank">Bank</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="gov-education">
                                    <label for="gov-education">Gov.Education Institute</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="gov-authorities">
                                    <label for="gov-authorities">Gov.Authorities / Institutes /Boards /Corporations</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="gov-departments">
                                    <label for="gov-departments">Gov. Departments / Ministries</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="foreign-missions">
                                    <label for="foreign-missions">Foreign Missions</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="religious">
                                    <label for="religious">Religious Institutes</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="ngo">
                                    <label for="ngo">NGO</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="port-city">
                                    <label for="port-city">Port City / Other Econ. Zone</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="gov-projects">
                                    <label for="gov-projects">Gov. Projects</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="other">
                                    <label for="other">Other</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">If Other, Please Specify</label>
                            <input type="text" class="form-input">
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
                                <input type="text" class="form-input" value="113865216">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Permit Number</label>
                                <input type="text" class="form-input">
                            </div>

                            <div class="form-group">
                                <label class="form-label">VAT Number <span class="required">*</span></label>
                                <input type="text" class="form-input" value="113865216V525">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Permit Expiration Date</label>
                                <input type="date" class="form-input">
                            </div>

                            <div class="form-group">
                                <label class="form-label">VAT Expiration Date</label>
                                <input type="date" class="form-input" value="2025-07-03">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Director Merchant Shipping License Number</label>
                                <input type="text" class="form-input">
                            </div>
                        </div>

                        <button class="submit-button">Save</button>
                        <div style="clear: both;"></div>
                    </div>
                </div>

                <!-- Owner Tab -->
                <div id="owner-tab" class="tab-content">
                    <!-- Owner tab content will be added in the next update -->
                </div>

                <!-- Attachment Tab -->
                <div id="attachment-tab" class="tab-content">
                    <!-- Attachment tab content will be added in the next update -->
                </div>

                <!-- Declaration Tab -->
                <div id="declaration-tab" class="tab-content">
                    <!-- Declaration tab content will be added in the next update -->
                </div>
            </div>

            <!-- Register Application Button -->
            <div style="text-align: right; margin-top: 20px;">
                <button style="background: #f39c12; color: white; padding: 15px 40px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: 600;" onclick="registerApplication()">Register Application</button>
            </div>
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

        function registerApplication() {
            // Validate required fields
            const requiredFields = document.querySelectorAll('input[required], select[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#e74c3c';
                    isValid = false;
                } else {
                    field.style.borderColor = '#bdc3c7';
                }
            });
            
            if (isValid) {
                alert('Application registered successfully! You will receive a confirmation email shortly.');
            } else {
                alert('Please fill in all required fields before submitting.');
            }
        }

        // File upload handling
        document.querySelectorAll('.file-choose-btn').forEach(button => {
            button.addEventListener('click', function() {
                const fileInput = this.parentElement.querySelector('.file-input');
                fileInput.click();
            });
        });

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

        // Upload button handling
        document.querySelectorAll('.file-upload-btn').forEach(button => {
            button.addEventListener('click', function() {
                const fileInput = this.parentElement.querySelector('.file-input');
                if (fileInput.files[0]) {
                    // Simulate file upload
                    this.textContent = 'Uploading...';
                    this.disabled = true;
                    
                    setTimeout(() => {
                        this.textContent = 'Uploaded';
                        this.style.background = '#27ae60';
                        this.disabled = false;
                    }, 2000);
                } else {
                    alert('Please select a file first.');
                }
            });
        });

        // View button handling
        document.querySelectorAll('.file-view-btn').forEach(button => {
            button.addEventListener('click', function() {
                alert('File viewer would open here in a real application.');
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

        // Mobile number formatting
        document.querySelectorAll('input[type="text"]').forEach(input => {
            if (input.placeholder && input.placeholder.includes('mobile') || 
                input.value && input.value.includes('+94')) {
                input.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    if (value.startsWith('94')) {
                        this.value = '+' + value;
                    } else if (value.startsWith('0')) {
                        this.value = '+94' + value.substring(1);
                    }
                });
            }
        });
    </script>
</body>
</html>
