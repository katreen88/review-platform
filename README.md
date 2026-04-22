# 🌐 Review Platform (API Version)

A modern full-stack web application that allows users to discover, review, and rate websites.

This version upgrades the project to an API-based architecture with dynamic frontend interactions and improved user experience.

---

## 🚀 Features

### 👤 Authentication & Security
- Secure Register / Login / Logout system
- Password hashing using `password_hash()`
- Session management
- CSRF protection
- Role-based access (Admin / User)

---

### 🌍 Website Management
- Admin can add new websites
- Unique website URLs
- Website details page with:
  - Title
  - URL
  - Description
  - Average rating
  - Total reviews

---

### ⭐ Reviews System
- Users can:
  - Add reviews (1–5 rating)
  - Edit their own reviews
  - Delete their own reviews
- Prevent duplicate reviews (one review per user per website)
- Admin can manage all reviews
- Reviews update dynamically (no page reload)

---

### 🔎 Search & UI
- Search websites by title, URL, or description
- Live search (real-time typing)
- Dynamic content loading using Fetch API
- Responsive UI using Bootstrap

---

### ⚡ API Integration (New)
- Converted core functionality to REST-style APIs:
  - Fetch websites
  - Fetch reviews
  - Add review
  - Add website
- Frontend communicates with backend using:
  - `fetch()`
  - `async/await`
- No full page reloads

---

## 🧱 Tech Stack

- **Frontend:** HTML, CSS, JavaScript (Fetch API)
- **Backend:** PHP 8+ (PDO)
- **Database:** MySQL / MariaDB
- **UI:** Bootstrap
- **Architecture:** API-based (REST-style)

---

## 📂 Project Structure
