# 🎓 EduCore ERP — Complete School Management System

A full-stack, production-ready School ERP built with **Laravel 11** (REST API) + **Vue 3** (SPA).

---

## 🚀 Quick Start

### Backend (Laravel 11)

```bash
cd backend
cp .env.example .env

# Edit .env — set DB_DATABASE, DB_USERNAME, DB_PASSWORD

composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
# ✅  API at http://localhost:8000/api
```

### Frontend (Vue 3)

```bash
cd frontend
cp .env.example .env
# VITE_API_URL=http://localhost:8000/api

npm install
npm run dev
# ✅  App at http://localhost:5173
```

---

## 🔑 Demo Login Credentials

| Role        | Email                      | Password |
|-------------|----------------------------|----------|
| Admin       | john@greenfield.edu        | password |
| Teacher     | sarah@greenfield.edu       | password |
| Student     | michael@student.edu        | password |
| Parent      | linda@email.com            | password |
| Accountant  | james@greenfield.edu       | password |
| Librarian   | grace@greenfield.edu       | password |
| Super Admin | alex@educore.io            | password |

---

## ✨ Features

### Admin
- Student management (photo, parent info, blood group, religion)
- Teacher management
- Classes & Arms (A/B/C) management
- WAEC Grading System (A1–F9) — fully configurable
- Results: enter scores, auto-grade, calculate positions, lock, publish
- Professional printable result sheets with PDF download
- Attendance management
- Finance (invoices, payments)
- Library management
- Announcements
- School Settings (logo, stamp, motto, session/term)

### Teacher
- Mark attendance (bulk, per class)
- Create & publish exams (CBT)
- Add questions (MCQ, True/False, Essay, Fill-in-blank)
- Upload course materials (PDF, PPT, DOC, Video, Links)
- View student performance

### Student
- Take CBT exams (timed, anti-cheat, question navigation)
- View results with WAEC grades
- Download/print result sheet (PDF)
- View course materials
- View attendance

### Result Sheet Includes
- School logo + stamp
- Student passport photo
- Student details (name, class, DOB, parent)
- Subject table: CA | Exam | Total | WAEC Grade | Remark | Position
- Summary: Total Score | Average | Class Position | Class Size
- Grading key
- Teacher & Principal signature lines
- Printable A4 layout

---

## 🗄️ Database Tables (16+)

schools, users, students, teachers, school_classes, subjects,
class_subjects, academic_sessions, terms, exams, questions,
exam_attempts, results, attendances, invoices, payments,
books, borrow_records, timetables, announcements,
notifications, materials, grading_systems, activity_logs

---

## 🛠️ Tech Stack

**Backend:** Laravel 11, PHP 8.2+, MySQL, Sanctum Auth, DomPDF

**Frontend:** Vue 3, Vite, Pinia, Vue Router 4, Axios, Chart.js, Font Awesome

---

## 📁 Project Structure

```
educore-erp/
├── backend/                  Laravel 11 API
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   │   ├── AllControllers.php    (15 controllers)
│   │   │   ├── NewControllers.php    (Settings, Grading, Enhanced Results, Students)
│   │   │   └── AuthController.php
│   │   └── Models/
│   │       └── AllModels.php         (20+ models)
│   ├── database/
│   │   ├── migrations/               (3 migration files)
│   │   └── seeders/                  (demo data + WAEC grades)
│   └── routes/api.php                (60+ endpoints)
│
└── frontend/                 Vue 3 SPA
    └── src/
        ├── views/
        │   ├── admin/       (14 views)
        │   ├── teacher/     (8 views)
        │   ├── student/     (8 views)
        │   ├── parent/      (2 views)
        │   ├── accountant/  (4 views)
        │   ├── librarian/   (5 views)
        │   ├── superadmin/  (4 views)
        │   ├── shared/      (Result sheet, Profile, Notifications)
        │   └── auth/        (Login)
        ├── services/api.js   (all API calls)
        ├── store/auth.js     (Pinia auth store)
        └── router/index.js   (role-based routing)
```

---

## 🚢 Deployment

### XAMPP (Windows / Local)
1. Copy `backend/` to `htdocs/educore_backend/`
2. Configure `.env` with DB credentials
3. Run `composer install && php artisan migrate --seed`
4. Build frontend: `npm run build` → copy `dist/` to `htdocs/educore_frontend/`

### KSWeb (Android Offline)
1. Copy `backend/` to `/sdcard/htdocs/educore_backend/`
2. Configure `.env`
3. Run migrations via KSWeb terminal
4. Build frontend on PC, copy `dist/` to `/sdcard/htdocs/educore_frontend/`

### cPanel Shared Hosting
1. Upload `backend/` outside `public_html`
2. Symlink `backend/public/` → `public_html/api/`
3. Build and upload frontend `dist/` to `public_html/`
4. Configure `.htaccess` for Vue Router

---

Built with ❤️ — EduCore ERP
