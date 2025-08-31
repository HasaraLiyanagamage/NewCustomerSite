<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/enhanced.css">
    <style>

/* Base Styles */
:root {
  --primary-color: #4361ee;
  --primary-light: #eef2ff;
  --primary-dark: #3a0ca3;
  --secondary-color: #3f37c9;
  --success-color: #10b981;
  --success-dark: #0d926c;
  --danger-color: #ef4444;
  --danger-dark: #dc2626;
  --warning-color: #f59e0b;
  --warning-dark: #d97706;
  --info-color: #3b82f6;
  --light-color: #f8f9fa;
  --dark-color: #1f2937;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
  --radius: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --radius-xl: 1rem;
  --radius-full: 9999px;
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  --transition: all 0.2s ease-in-out;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  line-height: 1.5;
  color: var(--gray-800);
  background-color: var(--gray-50);
}

a {
  text-decoration: none;
  color: var(--primary-color);
  transition: var(--transition);
}

a:hover {
  color: var(--secondary-color);
}

img {
  max-width: 100%;
  height: auto;
}

.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Typography */
h1, h2, h3, h4, h5, h6 {
  font-weight: 600;
  line-height: 1.25;
  margin-bottom: 0.75rem;
  color: var(--gray-900);
}

h1 { font-size: 2.25rem; }
h2 { font-size: 1.875rem; }
h3 { font-size: 1.5rem; }
h4 { font-size: 1.25rem; }
h5 { font-size: 1.125rem; }
h6 { font-size: 1rem; }

p {
  margin-bottom: 1rem;
}

.text-sm { font-size: 0.875rem; }
.text-base { font-size: 1rem; }
.text-lg { font-size: 1.125rem; }
.text-xl { font-size: 1.25rem; }
.text-2xl { font-size: 1.5rem; }
.text-3xl { font-size: 1.875rem; }
.text-4xl { font-size: 2.25rem; }

.font-light { font-weight: 300; }
.font-normal { font-weight: 400; }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }

.text-primary { color: var(--primary-color); }
.text-secondary { color: var(--secondary-color); }
.text-success { color: var(--success-color); }
.text-danger { color: var(--danger-color); }
.text-warning { color: var(--warning-color); }
.text-info { color: var(--info-color); }
.text-light { color: var(--light-color); }
.text-dark { color: var(--dark-color); }
.text-gray { color: var(--gray-500); }
.text-white { color: white; }

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  border-radius: var(--radius);
  font-weight: 500;
  font-size: 0.9375rem;
  line-height: 1.5;
  cursor: pointer;
  transition: var(--transition);
  border: 1px solid transparent;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.8125rem;
}

.btn-lg {
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.btn-primary:hover {
  background-color: var(--secondary-color);
  border-color: var(--secondary-color);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-secondary {
  background-color: var(--secondary-color);
  color: white;
  border-color: var(--secondary-color);
}

.btn-secondary:hover {
  background-color: var(--primary-dark);
  border-color: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-success {
  background-color: var(--success-color);
  color: white;
  border-color: var(--success-color);
}

.btn-success:hover {
  background-color: var(--success-dark);
  border-color: var(--success-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-danger {
  background-color: var(--danger-color);
  color: white;
  border-color: var(--danger-color);
}

.btn-danger:hover {
  background-color: var(--danger-dark);
  border-color: var(--danger-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-warning {
  background-color: var(--warning-color);
  color: white;
  border-color: var(--warning-color);
}

.btn-warning:hover {
  background-color: var(--warning-dark);
  border-color: var(--warning-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-outline {
  background-color: transparent;
  color: var(--primary-color);
  border-color: var(--primary-color);
}

.btn-outline:hover {
  background-color: var(--primary-color);
  color: white;
}

.btn-link {
  background-color: transparent;
  color: var(--primary-color);
  border-color: transparent;
  text-decoration: underline;
}

.btn-link:hover {
  color: var(--secondary-color);
  text-decoration: none;
}

.btn-icon {
  padding: 0.5rem;
  border-radius: 50%;
  width: 2.5rem;
  height: 2.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* Cards */
.card {
  background-color: white;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow);
  overflow: hidden;
  transition: var(--transition);
  border: 1px solid var(--gray-200);
}

.card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.card-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.card-body {
  padding: 1.5rem;
}

.card-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--gray-200);
  background-color: var(--gray-50);
}

/* Alerts */
.alert {
  padding: 1rem;
  border-radius: var(--radius);
  margin-bottom: 1rem;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.alert-icon {
  font-size: 1.25rem;
  margin-top: 0.125rem;
}

.alert-content {
  flex: 1;
}

.alert-primary {
  background-color: var(--primary-light);
  color: var(--primary-color);
  border-left: 4px solid var(--primary-color);
}

.alert-success {
  background-color: rgba(16, 185, 129, 0.1);
  color: var(--success-color);
  border-left: 4px solid var(--success-color);
}

.alert-danger {
  background-color: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  border-left: 4px solid var(--danger-color);
}

.alert-warning {
  background-color: rgba(245, 158, 11, 0.1);
  color: var(--warning-color);
  border-left: 4px solid var(--warning-color);
}

.alert-info {
  background-color: rgba(59, 130, 246, 0.1);
  color: var(--info-color);
  border-left: 4px solid var(--info-color);
}

/* Forms */
.form-group {
  margin-bottom: 1.25rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: var(--gray-700);
  font-size: 0.9375rem;
}

.form-control {
  display: block;
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 1rem;
  line-height: 1.5;
  color: var(--gray-800);
  background-color: white;
  background-clip: padding-box;
  border: 1px solid var(--gray-300);
  border-radius: var(--radius);
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
  border-color: var(--primary-color);
  outline: 0;
  box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.25);
}

.form-control-lg {
  padding: 1rem 1.25rem;
  font-size: 1.125rem;
}

.form-control-sm {
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
}

.input-group {
  position: relative;
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  width: 100%;
}

.input-group-text {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  color: var(--gray-700);
  text-align: center;
  white-space: nowrap;
  background-color: var(--gray-100);
  border: 1px solid var(--gray-300);
  border-radius: var(--radius);
}

.input-group-prepend {
  margin-right: -1px;
}

.input-group-append {
  margin-left: -1px;
}

.input-group > .form-control {
  position: relative;
  flex: 1 1 auto;
  width: 1%;
  min-width: 0;
  margin-bottom: 0;
}

.input-group > .form-control:not(:first-child),
.input-group > .custom-select:not(:first-child) {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

.input-group > .form-control:not(:last-child),
.input-group > .custom-select:not(:last-child) {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.form-text {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: var(--gray-600);
}

/* Tables */
.table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
  color: var(--gray-700);
}

.table th,
.table td {
  padding: 0.75rem 1rem;
  vertical-align: top;
  border-top: 1px solid var(--gray-200);
}

.table thead th {
  vertical-align: bottom;
  border-bottom: 2px solid var(--gray-200);
  font-weight: 600;
  color: var(--gray-700);
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
}

.table tbody + tbody {
  border-top: 2px solid var(--gray-200);
}

.table-striped tbody tr:nth-of-type(odd) {
  background-color: var(--gray-50);
}

.table-hover tbody tr:hover {
  background-color: var(--gray-100);
}

.table-responsive {
  display: block;
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Navigation */
.navbar {
  background-color: white;
  box-shadow: var(--shadow-sm);
  padding: 1rem 0;
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-logo {
  display: inline-flex;
  align-items: center;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--primary-color);
}

.nav-logo i {
  margin-right: 0.5rem;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-link {
  color: var(--gray-600);
  font-weight: 500;
  padding: 0.5rem 0;
  position: relative;
}

.nav-link:hover {
  color: var(--primary-color);
}

.nav-link.active {
  color: var(--primary-color);
  font-weight: 600;
}

.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: var(--primary-color);
}

/* Layout */
.flex {
  display: flex;
}

.flex-col {
  flex-direction: column;
}

.flex-row {
  flex-direction: row;
}

.items-center {
  align-items: center;
}

.justify-center {
  justify-content: center;
}

.justify-between {
  justify-content: space-between;
}

.justify-end {
  justify-content: flex-end;
}

.flex-1 {
  flex: 1;
}

.flex-grow {
  flex-grow: 1;
}

.w-full {
  width: 100%;
}

.h-full {
  height: 100%;
}

.min-h-screen {
  min-height: 100vh;
}

.p-0 { padding: 0; }
.p-1 { padding: 0.25rem; }
.p-2 { padding: 0.5rem; }
.p-3 { padding: 0.75rem; }
.p-4 { padding: 1rem; }
.p-5 { padding: 1.25rem; }
.p-6 { padding: 1.5rem; }
.p-8 { padding: 2rem; }
.p-10 { padding: 2.5rem; }

.py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
.py-5 { padding-top: 1.25rem; padding-bottom: 1.25rem; }
.py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.py-10 { padding-top: 2.5rem; padding-bottom: 2.5rem; }

.px-1 { padding-left: 0.25rem; padding-right: 0.25rem; }
.px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
.px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
.px-4 { padding-left: 1rem; padding-right: 1rem; }
.px-5 { padding-left: 1.25rem; padding-right: 1.25rem; }
.px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
.px-8 { padding-left: 2rem; padding-right: 2rem; }
.px-10 { padding-left: 2.5rem; padding-right: 2.5rem; }

.m-0 { margin: 0; }
.m-1 { margin: 0.25rem; }
.m-2 { margin: 0.5rem; }
.m-3 { margin: 0.75rem; }
.m-4 { margin: 1rem; }
.m-5 { margin: 1.25rem; }
.m-6 { margin: 1.5rem; }
.m-8 { margin: 2rem; }
.m-10 { margin: 2.5rem; }

.my-1 { margin-top: 0.25rem; margin-bottom: 0.25rem; }
.my-2 { margin-top: 0.5rem; margin-bottom: 0.5rem; }
.my-3 { margin-top: 0.75rem; margin-bottom: 0.75rem; }
.my-4 { margin-top: 1rem; margin-bottom: 1rem; }
.my-5 { margin-top: 1.25rem; margin-bottom: 1.25rem; }
.my-6 { margin-top: 1.5rem; margin-bottom: 1.5rem; }
.my-8 { margin-top: 2rem; margin-bottom: 2rem; }
.my-10 { margin-top: 2.5rem; margin-bottom: 2.5rem; }

.mx-1 { margin-left: 0.25rem; margin-right: 0.25rem; }
.mx-2 { margin-left: 0.5rem; margin-right: 0.5rem; }
.mx-3 { margin-left: 0.75rem; margin-right: 0.75rem; }
.mx-4 { margin-left: 1rem; margin-right: 1rem; }
.mx-5 { margin-left: 1.25rem; margin-right: 1.25rem; }
.mx-6 { margin-left: 1.5rem; margin-right: 1.5rem; }
.mx-8 { margin-left: 2rem; margin-right: 2rem; }
.mx-10 { margin-left: 2.5rem; margin-right: 2.5rem; }

.mt-1 { margin-top: 0.25rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-3 { margin-top: 0.75rem; }
.mt-4 { margin-top: 1rem; }
.mt-5 { margin-top: 1.25rem; }
.mt-6 { margin-top: 1.5rem; }
.mt-8 { margin-top: 2rem; }
.mt-10 { margin-top: 2.5rem; }

.mb-1 { margin-bottom: 0.25rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 0.75rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-5 { margin-bottom: 1.25rem; }
.mb-6 { margin-bottom: 1.5rem; }
.mb-8 { margin-bottom: 2rem; }
.mb-10 { margin-bottom: 2.5rem; }

.ml-1 { margin-left: 0.25rem; }
.ml-2 { margin-left: 0.5rem; }
.ml-3 { margin-left: 0.75rem; }
.ml-4 { margin-left: 1rem; }
.ml-5 { margin-left: 1.25rem; }
.ml-6 { margin-left: 1.5rem; }
.ml-8 { margin-left: 2rem; }
.ml-10 { margin-left: 2.5rem; }

.mr-1 { margin-right: 0.25rem; }
.mr-2 { margin-right: 0.5rem; }
.mr-3 { margin-right: 0.75rem; }
.mr-4 { margin-right: 1rem; }
.mr-5 { margin-right: 1.25rem; }
.mr-6 { margin-right: 1.5rem; }
.mr-8 { margin-right: 2rem; }
.mr-10 { margin-right: 2.5rem; }

/* Grid */
.grid {
  display: grid;
}

.grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
.grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.grid-cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
.grid-cols-6 { grid-template-columns: repeat(6, minmax(0, 1fr)); }

.gap-1 { gap: 0.25rem; }
.gap-2 { gap: 0.5rem; }
.gap-3 { gap: 0.75rem; }
.gap-4 { gap: 1rem; }
.gap-5 { gap: 1.25rem; }
.gap-6 { gap: 1.5rem; }
.gap-8 { gap: 2rem; }
.gap-10 { gap: 2.5rem; }

/* Hero Section */
.hero {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  color: white;
  padding: 6rem 0;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 50%;
  height: 100%;
  background: url('data:image/svg+xml;utf8,<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="100" cy="100" r="90" stroke="rgba(255,255,255,0.1)" stroke-width="2"/><circle cx="100" cy="100" r="60" stroke="rgba(255,255,255,0.1)" stroke-width="2"/><circle cx="100" cy="100" r="30" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>') no-repeat center right;
  opacity: 0.3;
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 800px;
  margin: 0 auto;
  padding: 0 1rem;
}

.hero h1 {
  font-size: 3rem;
  font-weight: 800;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.hero p {
  font-size: 1.25rem;
  opacity: 0.9;
  margin-bottom: 2rem;
}

/* Footer */
.footer {
  background-color: var(--gray-900);
  color: white;
  padding: 4rem 0 2rem;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.footer-section h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  color: white;
}

.footer-links {
  list-style: none;
}

.footer-links li {
  margin-bottom: 0.75rem;
}

.footer-links a {
  color: var(--gray-400);
  transition: var(--transition);
}

.footer-links a:hover {
  color: white;
}

.footer-bottom {
  text-align: center;
  padding-top: 2rem;
  border-top: 1px solid var(--gray-800);
  color: var(--gray-400);
  font-size: 0.875rem;
}

/* Dashboard Specific Styles */
.dashboard-grid {
  display: grid;
  grid-template-columns: 280px 1fr;
  min-height: 100vh;
}

.sidebar {
  background-color: white;
  border-right: 1px solid var(--gray-200);
  padding: 1.5rem 0;
}

.sidebar-menu {
  padding: 0 1.5rem;
}

.sidebar-title {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--gray-500);
  margin: 1.5rem 0 0.5rem;
  padding: 0 1.5rem;
}

.sidebar-link {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  color: var(--gray-600);
  font-weight: 500;
  border-radius: var(--radius);
  transition: var(--transition);
}

.sidebar-link:hover {
  background-color: var(--gray-100);
  color: var(--primary-color);
}

.sidebar-link.active {
  background-color: var(--primary-light);
  color: var(--primary-color);
  font-weight: 600;
}

.sidebar-link i {
  margin-right: 0.75rem;
  width: 20px;
  text-align: center;
  font-size: 1.1rem;
}

.main-content {
  padding: 2rem;
}

.welcome-banner {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  border-radius: var(--radius-lg);
  padding: 2rem;
  color: white;
  margin-bottom: 2rem;
  position: relative;
  overflow: hidden;
}

.welcome-banner h1 {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: white;
}

.welcome-banner p {
  margin: 0;
  opacity: 0.9;
  font-size: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: var(--radius-md);
  padding: 1.5rem;
  box-shadow: var(--shadow);
  transition: var(--transition);
  border: 1px solid var(--gray-200);
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

.stat-card.primary {
  border-left: 4px solid var(--primary-color);
}

.stat-card.success {
  border-left: 4px solid var(--success-color);
}

.stat-card.warning {
  border-left: 4px solid var(--warning-color);
}

.stat-card.danger {
  border-left: 4px solid var(--danger-color);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

.stat-card.primary .stat-icon {
  background-color: rgba(67, 97, 238, 0.1);
  color: var(--primary-color);
}

.stat-card.success .stat-icon {
  background-color: rgba(16, 185, 129, 0.1);
  color: var(--success-color);
}

.stat-card.warning .stat-icon {
  background-color: rgba(245, 158, 11, 0.1);
  color: var(--warning-color);
}

.stat-card.danger .stat-icon {
  background-color: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
}

.stat-card h3 {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--gray-500);
  margin: 0 0 0.5rem;
}

.stat-number {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--gray-800);
  margin: 0 0 1rem;
  line-height: 1.2;
}

.stat-desc {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  color: var(--gray-500);
}

.trend-up {
  color: var(--success-color);
  margin-right: 0.5rem;
  display: flex;
  align-items: center;
}

.trend-down {
  color: var(--danger-color);
  margin-right: 0.5rem;
  display: flex;
  align-items: center;
}

/* Responsive */
@media (max-width: 1024px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
  
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 280px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: 1000;
  }
  
  .sidebar.active {
    transform: translateX(0);
  }
  
  .main-content {
    margin-left: 0;
    padding: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .hero h1 {
    font-size: 2.25rem;
  }
  
  .hero p {
    font-size: 1.125rem;
  }
  
  .footer-content {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 640px) {
  .nav-links {
    gap: 1rem;
  }
  
  .footer-content {
    grid-template-columns: 1fr;
  }
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}

/* Utility Classes */
.bg-white { background-color: white; }
.bg-gray-50 { background-color: var(--gray-50); }
.bg-gray-100 { background-color: var(--gray-100); }
.bg-gray-200 { background-color: var(--gray-200); }
.bg-gray-300 { background-color: var(--gray-300); }
.bg-gray-400 { background-color: var(--gray-400); }
.bg-gray-500 { background-color: var(--gray-500); }
.bg-gray-600 { background-color: var(--gray-600); }
.bg-gray-700 { background-color: var(--gray-700); }
.bg-gray-800 { background-color: var(--gray-800); }
.bg-gray-900 { background-color: var(--gray-900); }
.bg-primary { background-color: var(--primary-color); }
.bg-secondary { background-color: var(--secondary-color); }
.bg-success { background-color: var(--success-color); }
.bg-danger { background-color: var(--danger-color); }
.bg-warning { background-color: var(--warning-color); }
.bg-info { background-color: var(--info-color); }
.bg-light { background-color: var(--light-color); }
.bg-dark { background-color: var(--dark-color); }

.rounded { border-radius: var(--radius); }
.rounded-md { border-radius: var(--radius-md); }
.rounded-lg { border-radius: var(--radius-lg); }
.rounded-xl { border-radius: var(--radius-xl); }
.rounded-full { border-radius: var(--radius-full); }

.shadow { box-shadow: var(--shadow); }
.shadow-md { box-shadow: var(--shadow-md); }
.shadow-lg { box-shadow: var(--shadow-lg); }
.shadow-xl { box-shadow: var(--shadow-xl); }
.shadow-none { box-shadow: none; }

.text-center { text-align: center; }
.text-left { text-align: left; }
.text-right { text-align: right; }

.hidden { display: none; }
.block { display: block; }
.inline-block { display: inline-block; }
.flex { display: flex; }
.inline-flex { display: inline-flex; }

/* Custom Components */
.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: var(--primary-color);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background-color: var(--danger-color);
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--gray-100);
}

::-webkit-scrollbar-thumb {
  background: var(--gray-400);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: var(--gray-500);
}
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <!-- Navigation -->
    <nav class="navbar animate-fade-in">
        <div class="container flex justify-between items-center">
            <a href="index.php" class="nav-logo">
                <i class="fas fa-users-cog"></i>
                <span>CustomerPro</span>
            </a>
            
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                    <a href="customers.php" class="nav-link <?= in_array($current_page, ['customers.php', 'view_customer.php', 'edit_customer.php']) ? 'active' : '' ?>">
                        <i class="fas fa-users mr-1"></i> Customers
                    </a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="employees.php" class="nav-link <?= in_array($current_page, ['employees.php', 'edit_employee.php']) ? 'active' : '' ?>">
                            <i class="fas fa-user-tie mr-1"></i> Employees
                        </a>
                    <?php endif; ?>
                    <a href="profile.php" class="nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-circle mr-1"></i> Profile
                    </a>
                    <a href="logout.php" class="btn btn-sm btn-outline">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-link <?= $current_page === 'login.php' ? 'active' : '' ?>">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                    <a href="register.php" class="nav-link <?= $current_page === 'register.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-plus mr-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="container mt-4">
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']); ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="container mt-4">
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Hero Section for Non-Logged In Users -->
            <section class="hero">
                <div class="hero-content">
                    <h1>Streamline Your Customer Management</h1>
                    <p>Efficiently manage your customers, track interactions, and grow your business with our comprehensive customer management solution.</p>
                    <div class="flex justify-center gap-4 mt-8">
                        <a href="register_customer.php" class="btn btn-lg btn-outline" style="background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.3); color: white;">
                            <i class="fas fa-user-plus mr-2"></i> Register as Customer
                        </a>
                        <a href="login.php" class="btn btn-lg btn-outline" style="background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.3); color: white;">
                            <i class="fas fa-sign-in-alt mr-2"></i> Employee Login
                        </a>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="py-16 bg-white">
                <div class="container">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold mb-4">Powerful Features</h2>
                        <p class="text-gray-600 max-w-2xl mx-auto">Everything you need to manage your customer relationships effectively</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="card p-6 text-center">
                            <div class="text-4xl text-primary-color mb-4">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Customer Management</h3>
                            <p class="text-gray-600">Easily manage customer information, interactions, and history in one place.</p>
                        </div>
                        
                        <div class="card p-6 text-center">
                            <div class="text-4xl text-primary-color mb-4">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Analytics</h3>
                            <p class="text-gray-600">Gain insights with powerful analytics and reporting tools.</p>
                        </div>
                        
                        <div class="card p-6 text-center">
                            <div class="text-4xl text-primary-color mb-4">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Responsive Design</h3>
                            <p class="text-gray-600">Access your data from anywhere, on any device.</p>
                        </div>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <!-- Dashboard Content for Logged In Users -->
            <div class="container py-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold">Welcome Back, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>!</h1>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="customers_new.php" class="btn">
                            <i class="fas fa-plus mr-2"></i> Add New Customer
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="card p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Customers</p>
                                <h3 class="text-2xl font-bold">1,248</h3>
                                <p class="text-sm text-green-500">+12% from last month</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <i class="fas fa-chart-line text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Active Projects</p>
                                <h3 class="text-2xl font-bold">42</h3>
                                <p class="text-sm text-green-500">+3 this week</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                                <i class="fas fa-tasks text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Pending Tasks</p>
                                <h3 class="text-2xl font-bold">8</h3>
                                <p class="text-sm text-red-500">-2 from yesterday</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold">Recent Activity</h2>
                        <a href="#" class="text-sm text-primary-color hover:underline">View All</a>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start pb-4 border-b border-gray-100">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-full mr-4">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">New customer added</p>
                                <p class="text-sm text-gray-500">Acme Corporation was added to the system</p>
                                <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start pb-4 border-b border-gray-100">
                            <div class="p-2 bg-green-100 text-green-600 rounded-full mr-4">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">New invoice generated</p>
                                <p class="text-sm text-gray-500">Invoice #INV-2023-045 for $1,250.00</p>
                                <p class="text-xs text-gray-400 mt-1">5 hours ago</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="p-2 bg-yellow-100 text-yellow-600 rounded-full mr-4">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">Upcoming meeting</p>
                                <p class="text-sm text-gray-500">Meeting with John Doe at 2:00 PM tomorrow</p>
                                <p class="text-xs text-gray-400 mt-1">1 day ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>CustomerPro</h3>
                    <p class="mt-2 text-gray-400">Streamline your customer management and grow your business with our comprehensive solution.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="features.php">Features</a></li>
                        <li><a href="pricing.php">Pricing</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Legal</h3>
                    <ul class="footer-links">
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                        <li><a href="cookies.php">Cookie Policy</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope mr-2"></i> info@customerpro.com</li>
                        <li><i class="fas fa-phone mr-2"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> 123 Business St, City, Country</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Customer Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Add active class to current nav link
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = '<?= $current_page ?>';
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
            
            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
