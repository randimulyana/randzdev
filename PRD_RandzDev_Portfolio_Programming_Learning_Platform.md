# PRD — Personal Developer Portfolio & Programming Learning Platform

**Project Name:** Randz.dev  
**Project Type:** Personal Developer Portfolio + Programming Blog + Learning Platform  
**Status:** Draft — Ready for Development Planning  
**Backend:** Laravel  
**Frontend:** Laravel Blade  
**Database:** MySQL  
**Authentication:** Laravel Breeze  
**Authorization:** Spatie Laravel Permission  
**Asset Bundler:** Vite  
**Frontend Approach:** Blade + Tailwind CSS + Vanilla JavaScript  
**Architecture:** Server-rendered Laravel MVC  
**Target:** Production-ready personal developer website

---

## 1. Executive Summary

Randz.dev adalah website personal developer yang menggabungkan tiga fungsi utama:

1. Personal Developer Portfolio
2. Programming Blog & Technical Documentation
3. Programming Learning Platform untuk Pemula

Website tidak hanya digunakan untuk menampilkan identitas, skill, pengalaman, dan project pemilik website, tetapi juga menjadi tempat untuk mendokumentasikan proses belajar programming melalui artikel, tutorial, roadmap, dan learning path.

Konsep utama produk:

> **Build. Learn. Document. Share.**

Pemilik website dapat membangun project, mendokumentasikan proses dan pengetahuannya melalui artikel/tutorial, kemudian mengorganisasikannya menjadi learning path yang dapat diikuti oleh pengunjung.

---

## 2. Product Vision

Membangun personal developer website yang bukan sekadar portfolio statis, tetapi menjadi pusat identitas, karya, pengetahuan, dan dokumentasi pembelajaran programming.

Website harus berkembang bersama pemiliknya.

Ketika pemilik:

- mempelajari teknologi baru;
- membuat project baru;
- menemukan masalah;
- menyelesaikan masalah;
- mempelajari konsep programming;
- membuat tutorial;

semuanya dapat didokumentasikan di website.

```text
                    RANDZ.DEV
                       |
        +--------------+--------------+
        |              |              |
        v              v              v
    PORTFOLIO        BLOG           LEARN
        |              |              |
     Projects       Articles       Roadmaps
     Skills         Tutorials     Learning Paths
     Experience     Notes         Lessons
        |              |              |
        +--------------+--------------+
                       |
                       v
               PERSONAL BRAND
```

---

## 3. Product Goals

### 3.1 Primary Goals

Website harus:

- menampilkan portfolio profesional;
- memperlihatkan kemampuan teknis;
- menampilkan project yang pernah dibuat;
- menyediakan artikel programming;
- menyediakan tutorial programming;
- menyediakan learning path untuk pemula;
- menyediakan dokumentasi teknis;
- mudah dikelola melalui admin CMS;
- SEO-friendly;
- responsive;
- cepat;
- mudah dikembangkan;
- memiliki fondasi authorization yang baik;
- dapat berkembang menjadi platform pembelajaran yang lebih lengkap.

### 3.2 Secondary Goals

Website juga diharapkan:

- mendukung personal branding;
- menjadi dokumentasi perjalanan belajar;
- menjadi tempat showcase project;
- menjadi sumber referensi bagi developer pemula;
- menjadi pusat konten yang dapat dibagikan melalui Google/social media.

---

## 4. Non-Goals

Fitur berikut tidak menjadi fokus MVP:

- marketplace course;
- pembayaran;
- subscription;
- video hosting;
- live coding;
- forum komunitas;
- social network;
- gamification kompleks;
- sertifikat;
- sistem ujian kompleks;
- mobile application;
- microservices;
- SPA;
- React;
- Vue;
- Livewire;
- Inertia;
- Filament.

MVP harus tetap menjadi aplikasi Laravel monolith yang sederhana dan maintainable.

---

## 5. Target Users

### 5.1 Visitor

Pengunjung umum yang ingin:

- mengenal developer;
- melihat portfolio;
- membaca artikel;
- mempelajari programming;
- mengikuti tutorial;
- melihat learning path.

Visitor tidak wajib login untuk membaca konten publik.

### 5.2 Registered User

Akan digunakan pada fase berikutnya.

User dapat:

- memiliki profile;
- menyimpan bookmark;
- mencatat progress belajar;
- menandai lesson sebagai selesai.

Fitur ini belum menjadi fokus utama MVP.

### 5.3 Author

Author adalah pengguna yang memiliki izin membuat dan mengelola konten seperti:

- artikel;
- tutorial;
- learning material.

Role ini memungkinkan website berkembang menjadi platform dengan beberapa contributor.

### 5.4 Administrator

Administrator mempunyai akses penuh terhadap sistem.

Administrator dapat:

- mengelola user;
- mengelola role;
- mengelola permission;
- mengelola artikel;
- mengelola kategori;
- mengelola tag;
- mengelola project;
- mengelola learning path;
- mengelola lesson;
- mengelola profile website;
- mengelola konfigurasi tertentu.

---

## 6. Role & Permission

Authorization menggunakan:

**Spatie Laravel Permission**

Role awal:

```text
admin
author
user
```

MVP dapat dimulai hanya dengan:

```text
admin
```

tetapi struktur database dan permission harus sudah mendukung role lainnya.

### 6.1 Permission Convention

Permission menggunakan pola:

```text
resource.action
```

Contoh:

```text
posts.view
posts.create
posts.update
posts.delete
posts.publish

projects.view
projects.create
projects.update
projects.delete

categories.view
categories.create
categories.update
categories.delete

tags.view
tags.create
tags.update
tags.delete

learning_paths.view
learning_paths.create
learning_paths.update
learning_paths.delete

lessons.view
lessons.create
lessons.update
lessons.delete

users.view
users.create
users.update
users.delete

roles.view
roles.create
roles.update
roles.delete
```

### 6.2 Permission Matrix

| Resource | Admin | Author | User |
|---|---:|---:|---:|
| View Posts | ✓ | ✓ | ✓ |
| Create Posts | ✓ | ✓ | - |
| Update Posts | ✓ | ✓ | - |
| Delete Posts | ✓ | ✓ | - |
| Publish Posts | ✓ | optional | - |
| Manage Projects | ✓ | - | - |
| Manage Categories | ✓ | optional | - |
| Manage Tags | ✓ | optional | - |
| Manage Learning Paths | ✓ | ✓ | - |
| Manage Lessons | ✓ | ✓ | - |
| Manage Users | ✓ | - | - |
| Manage Roles | ✓ | - | - |
| Learning Progress | ✓ | ✓ | ✓ |
| Bookmark | ✓ | ✓ | ✓ |

Authorization tidak boleh hanya mengandalkan pemeriksaan role.

Gunakan:

- middleware;
- permission;
- policies;
- Gates jika diperlukan.

---

## 7. Product Information Architecture

Public website:

```text
/
├── /about
├── /projects
│   └── /projects/{project}
├── /blog
│   ├── /blog/{post}
│   ├── /categories/{category}
│   └── /tags/{tag}
├── /learn
│   └── /learn/{learning-path}
│       └── /learn/{learning-path}/{lesson}
└── /contact
```

Authentication:

```text
/login
/register
/forgot-password
/reset-password
```

Admin:

```text
/admin
/admin/posts
/admin/categories
/admin/tags
/admin/projects
/admin/technologies
/admin/learning-paths
/admin/lessons
/admin/users
/admin/roles
/admin/permissions
/admin/settings
```

---

## 8. Global Navigation

Navbar public:

```text
Home
About
Projects
Blog
Learn
Contact
```

CTA utama:

```text
Start Learning
```

atau:

```text
View Projects
```

Navigation harus responsive pada mobile.

---

## 9. Homepage

Homepage adalah halaman utama personal brand.

Urutan section:

```text
Hero
↓
Introduction
↓
Featured Projects
↓
Learning Section
↓
Latest Articles
↓
Learning Paths
↓
Skills
↓
About / Developer Journey
↓
CTA
↓
Footer
```

### 9.1 Hero

Isi:

- nama;
- role;
- short description;
- CTA;
- social links.

Contoh konsep:

```text
Hi, I'm Randi.

Web Developer & Builder

I build web applications and document what
I learn about programming.

[View Projects]
[Start Learning]
```

Konten aktual harus dikelola dari database/configuration, bukan hard-coded jika memungkinkan.

---

## 10. About Page

URL:

```text
/about
```

Section:

### Profile

- name;
- profile photo;
- short bio;
- location;
- education;
- current focus.

### Developer Journey

Timeline perjalanan:

```text
Started Learning
        ↓
First Web Project
        ↓
Learning PHP
        ↓
Learning Laravel
        ↓
Building Production Projects
        ↓
Documenting & Sharing
```

---

## 11. Skills

Skills dikelompokkan berdasarkan kategori.

Contoh:

```text
Programming
- PHP
- JavaScript
- HTML
- CSS
- SQL

Framework
- Laravel

Tools
- Git
- GitHub
- Docker
- Linux
- VS Code
```

Jangan menggunakan angka seperti:

```text
Laravel 90%
PHP 80%
```

Gunakan kategori dan deskripsi.

---

## 12. Projects

URL:

```text
/projects
```

Project ditampilkan dalam bentuk card.

Setiap project minimal mempunyai:

```text
title
slug
short_description
description
thumbnail
status
role
repository_url
live_url
started_at
completed_at
featured
published
```

Project dapat memiliki:

```text
Technologies
Features
Challenges
Solutions
Screenshots
```

---

## 13. Project Detail / Case Study

URL:

```text
/projects/{project}
```

Struktur:

```text
Project Overview
↓
Problem
↓
Solution
↓
Features
↓
Technology Stack
↓
Architecture
↓
Challenges
↓
Solutions
↓
Screenshots
↓
Result
↓
Repository / Live Demo
```

Tujuan halaman ini adalah memperlihatkan kemampuan engineering, bukan hanya screenshot.

---

## 14. Blog

URL:

```text
/blog
```

Blog merupakan pusat artikel dan dokumentasi.

Jenis konten dapat dibedakan menjadi:

```text
Article
Tutorial
Note
Guide
Case Study
```

Setiap post mempunyai:

```text
title
slug
excerpt
content
featured_image
category_id
author_id
status
published_at
reading_time
meta_title
meta_description
canonical_url
```

Status:

```text
draft
published
archived
```

---

## 15. Categories

Contoh kategori:

```text
PHP
Laravel
JavaScript
Database
Git
Linux
Docker
Web Development
Programming Basics
Career
```

Category mempunyai:

```text
name
slug
description
meta_title
meta_description
```

---

## 16. Tags

Post dapat memiliki banyak tag.

Contoh:

```text
#php
#laravel
#mysql
#docker
#git
#beginner
#web-development
```

Relationship:

```text
posts
   ↕
post_tag
   ↕
tags
```

---

## 17. Search

Public website menyediakan search.

Search dapat mencari:

- post title;
- excerpt;
- content;
- category;
- tag;
- project title.

MVP menggunakan database search sederhana.

Search harus:

- cepat;
- memiliki pagination;
- menampilkan hasil relevan;
- menangani keyword kosong;
- memiliki empty state.

---

## 18. Learning Platform

Learning merupakan fitur utama yang membedakan website dari portfolio biasa.

URL:

```text
/learn
```

Halaman menampilkan:

```text
Learning Paths
```

Contoh:

```text
PHP untuk Pemula
Web Development untuk Pemula
Laravel untuk Pemula
Git & GitHub
```

---

## 19. Learning Path

Learning Path adalah kumpulan lesson yang disusun secara berurutan.

Contoh:

```text
PHP untuk Pemula

01 Introduction
02 Installation
03 Variables
04 Data Types
05 Operators
06 Conditional
07 Loop
08 Function
09 Array
10 OOP
11 Error Handling
12 Mini Project
```

Entity:

```text
learning_paths
```

Field:

```text
id
title
slug
description
thumbnail
difficulty
estimated_duration
status
published_at
created_by
updated_by
created_at
updated_at
```

Difficulty:

```text
beginner
intermediate
advanced
```

---

## 20. Lesson

Lesson adalah unit terkecil dalam learning path.

Field:

```text
id
learning_path_id
title
slug
excerpt
content
order
status
published_at
created_by
updated_by
created_at
updated_at
```

Lesson mempunyai navigation:

```text
Previous Lesson
Current Lesson
Next Lesson
```

---

## 21. Lesson Content

Lesson harus mendukung:

- heading;
- paragraph;
- list;
- image;
- link;
- quote;
- code block;
- table;
- note;
- warning;
- example.

Programming content harus mempunyai syntax highlighting.

Contoh:

```php
$name = "Randi";

echo "Hello " . $name;
```

Code block harus mendukung:

- language label;
- copy button;
- syntax highlighting.

---

## 22. Learning Path UX

Halaman learning path:

```text
PHP untuk Pemula

Description

Difficulty:
Beginner

Estimated:
4 hours

Lessons:
✓ 01 Introduction
✓ 02 Installation
○ 03 Variables
○ 04 Data Types
○ 05 Operators

[Start Learning]
```

Jika visitor belum login, Start Learning langsung membuka lesson.

Tidak boleh memaksa login hanya untuk membaca materi.

---

## 23. User Learning Progress

Fitur ini merupakan Phase 2.

Ketika user login:

```text
PHP Beginner
██████░░░░ 60%

6 / 10 lessons completed
```

Database:

```text
lesson_progress
```

Field:

```text
id
user_id
lesson_id
completed_at
created_at
updated_at
```

Unique constraint:

```text
user_id + lesson_id
```

---

## 24. Bookmark

Phase 2.

User dapat menyimpan:

```text
Bookmark Article
Bookmark Lesson
Bookmark Project
```

Contoh:

```text
bookmarks
```

dengan polymorphic relation.

---

## 25. Authentication

Gunakan:

**Laravel Breeze**

Authentication harus mencakup:

```text
Login
Logout
Register
Forgot Password
Reset Password
Email Verification
```

Jika registration belum diperlukan pada MVP, route registration dapat dinonaktifkan tanpa menghapus fondasi Breeze.

Frontend tetap menggunakan Blade.

Tidak menggunakan:

```text
React
Vue
Livewire
Inertia
Filament
```

---

## 26. Admin Dashboard

URL:

```text
/admin
```

Dashboard menampilkan:

```text
Total Posts
Published Posts
Draft Posts
Total Projects
Learning Paths
Lessons
Users
```

Dashboard juga dapat menampilkan:

```text
Recent Posts
Recent Projects
Recent Users
```

Tidak perlu analytics kompleks pada MVP.

---

## 27. Admin — Post Management

Admin dapat:

```text
List Posts
Create Post
Edit Post
Delete Post
Publish Post
Unpublish Post
```

List memiliki:

```text
Title
Category
Author
Status
Published At
Actions
```

Filtering:

```text
Status
Category
Author
```

Search:

```text
Title
```

---

## 28. Admin — Project Management

CRUD:

```text
Create
Read
Update
Delete
```

Admin dapat:

- upload thumbnail;
- menambahkan technologies;
- memasukkan repository URL;
- memasukkan live URL;
- menentukan featured;
- publish/unpublish.

---

## 29. Admin — Learning Path Management

Admin dapat:

```text
Create
Edit
Delete
Publish
Reorder
```

Learning path dapat memiliki banyak lesson.

Admin harus dapat mengatur urutan lesson.

---

## 30. Admin — Lesson Management

CRUD:

```text
Create
Edit
Delete
Publish
Reorder
```

Lesson harus terhubung ke learning path.

Admin dapat melihat:

```text
Learning Path
    ├── Lesson 1
    ├── Lesson 2
    ├── Lesson 3
    └── Lesson 4
```

---

## 31. Admin — User Management

Admin dapat:

```text
View Users
Create User
Edit User
Delete User
Assign Role
Remove Role
```

User list:

```text
Name
Email
Role
Status
Created At
```

---

## 32. Admin — Role & Permission

Menggunakan:

**Spatie Laravel Permission**

Admin dapat mengelola:

```text
Roles
Permissions
Role Permissions
User Roles
```

Authorization wajib dilakukan di server-side.

---

## 33. Contact

URL:

```text
/contact
```

Field:

```text
name
email
subject
message
```

Validation:

```text
name: required
email: required|email
subject: required
message: required
```

Pesan disimpan di:

```text
contact_messages
```

Admin dapat melihat pesan dari dashboard.

---

## 34. Database Schema

### users

```text
id
name
email
email_verified_at
password
remember_token
created_at
updated_at
```

### profiles

```text
id
user_id
headline
bio
avatar
location
website
github_url
linkedin_url
instagram_url
created_at
updated_at
```

### posts

```text
id
author_id
category_id
title
slug
excerpt
content
featured_image
status
reading_time
meta_title
meta_description
canonical_url
published_at
created_at
updated_at
deleted_at
```

### categories

```text
id
name
slug
description
meta_title
meta_description
created_at
updated_at
```

### tags

```text
id
name
slug
created_at
updated_at
```

### post_tag

```text
post_id
tag_id
```

Unique:

```text
post_id + tag_id
```

### projects

```text
id
title
slug
short_description
description
thumbnail
role
status
repository_url
live_url
started_at
completed_at
featured
published_at
created_at
updated_at
deleted_at
```

### technologies

```text
id
name
slug
icon
created_at
updated_at
```

### project_technology

```text
project_id
technology_id
```

### learning_paths

```text
id
title
slug
description
thumbnail
difficulty
estimated_duration
status
published_at
created_by
updated_by
created_at
updated_at
deleted_at
```

### lessons

```text
id
learning_path_id
title
slug
excerpt
content
order
status
published_at
created_by
updated_by
created_at
updated_at
deleted_at
```

### contact_messages

```text
id
name
email
subject
message
read_at
created_at
updated_at
```

---

## 35. Future Tables

Tidak wajib dibuat pada MVP:

```text
lesson_progress
bookmarks
comments
media
user_profiles
notifications
```

---

## 36. Database Rules

Wajib menggunakan:

- foreign keys;
- indexes;
- unique constraints;
- nullable fields hanya jika memang diperlukan;
- timestamps;
- soft deletes untuk content yang membutuhkan recovery;
- enum/string convention yang konsisten.

Index minimal:

```text
posts.slug
posts.status
posts.published_at
posts.category_id

categories.slug
tags.slug

projects.slug
projects.published_at

learning_paths.slug
learning_paths.status

lessons.learning_path_id
lessons.order
```

---

## 37. Laravel Architecture

Project menggunakan Laravel MVC dengan separation of concerns.

Struktur:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Public/
│   │   └── Admin/
│   ├── Requests/
│   └── Middleware/
├── Models/
├── Policies/
├── Services/
└── Support/
```

---

## 38. Controller Rules

Controller harus tipis.

Business logic kompleks tidak boleh diletakkan di controller.

Controller bertugas:

```text
Receive Request
↓
Validate
↓
Call Service / Model operation
↓
Return Response
```

---

## 39. Form Requests

Semua form penting menggunakan Form Request.

Contoh:

```text
StorePostRequest
UpdatePostRequest

StoreProjectRequest
UpdateProjectRequest

StoreLearningPathRequest
UpdateLearningPathRequest

StoreLessonRequest
UpdateLessonRequest

ContactMessageRequest
```

---

## 40. Service Layer

Service digunakan untuk business logic yang cukup kompleks.

Contoh:

```text
PostService
ProjectService
LearningPathService
LessonService
```

Contoh:

```php
$postService->publish($post);
```

Tidak semua CRUD wajib memiliki service jika operasinya sangat sederhana.

Prinsip:

> Jangan membuat abstraction hanya demi abstraction.

---

## 41. Policies

Gunakan Laravel Policies untuk authorization resource-level.

Contoh:

```text
PostPolicy
ProjectPolicy
LearningPathPolicy
LessonPolicy
UserPolicy
```

Contoh rule:

```text
Admin:
    full access

Author:
    manage own content

User:
    read published content
```

---

## 42. Blade Architecture

Gunakan Blade Components.

Contoh:

```text
resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── admin.blade.php
│   └── guest.blade.php
├── components/
│   ├── navbar.blade.php
│   ├── footer.blade.php
│   ├── post-card.blade.php
│   ├── project-card.blade.php
│   ├── lesson-card.blade.php
│   └── pagination.blade.php
├── pages/
│   ├── home.blade.php
│   ├── about.blade.php
│   └── contact.blade.php
├── blog/
├── projects/
├── learn/
└── admin/
```

---

## 43. Frontend

Frontend menggunakan:

```text
Blade
Tailwind CSS
Vanilla JavaScript
Vite
```

Tidak menggunakan SPA framework.

UI harus:

- responsive;
- accessible;
- readable;
- consistent;
- fast.

---

## 44. Design System

Komponen:

```text
Button
Input
Textarea
Select
Badge
Card
Alert
Modal
Dropdown
Pagination
Breadcrumb
Tabs
Code Block
Toast
Empty State
Loading State
```

---

## 45. Responsive Design

Minimal mendukung:

```text
Mobile
Tablet
Desktop
Large Desktop
```

Mobile navigation menggunakan menu toggle.

Content artikel harus nyaman dibaca pada layar kecil.

---

## 46. SEO Requirements

Setiap public content harus memiliki:

```text
title
meta description
canonical URL
Open Graph
Twitter/X card
```

URL harus menggunakan slug.

Contoh:

```text
/blog/belajar-php-untuk-pemula
/learn/php-dasar/variables
/projects/sim-pkl
```

---

## 47. Sitemap

Generate:

```text
/sitemap.xml
```

Minimal mencakup:

```text
Home
About
Projects
Published Projects
Blog
Published Posts
Learning Paths
Published Lessons
```

Draft tidak boleh masuk sitemap.

---

## 48. Robots

Website menyediakan:

```text
/robots.txt
```

Admin route tidak boleh diindeks.

---

## 49. Performance Requirements

Target:

- server-rendered;
- minimal JavaScript;
- image optimization;
- lazy loading image;
- pagination;
- query optimization;
- eager loading relationship;
- database indexes;
- caching bila diperlukan.

Hindari N+1 queries.

Gunakan eager loading jika relationship diperlukan.

---

## 50. Caching

Caching tidak perlu berlebihan.

Gunakan cache untuk data yang relatif jarang berubah seperti:

```text
Categories
Tags
Technologies
Site Settings
```

Cache dapat diperkenalkan setelah baseline application berjalan.

---

## 51. Security

Wajib:

- CSRF protection;
- authentication;
- authorization;
- Form Request validation;
- password hashing;
- mass-assignment protection;
- escaped Blade output;
- secure file upload;
- authorization server-side;
- rate limiting untuk endpoint sensitif.

Konten HTML yang berasal dari editor harus diproses secara aman.

---

## 52. File Upload

Upload digunakan untuk:

- profile image;
- project thumbnail;
- project screenshots;
- post featured image;
- learning thumbnail.

File harus:

- divalidasi;
- memiliki batas ukuran;
- menggunakan MIME validation;
- memiliki nama file aman;
- disimpan menggunakan Laravel Storage.

---

## 53. Content Management Strategy

Konten dibedakan:

```text
Portfolio Content
    ├── Profile
    ├── Skills
    ├── Projects
    └── Experience

Knowledge Content
    ├── Posts
    ├── Tutorials
    ├── Notes
    └── Guides

Learning Content
    ├── Learning Paths
    └── Lessons
```

---

## 54. Publishing Workflow

Content memiliki lifecycle:

```text
Draft
  ↓
Review
  ↓
Published
  ↓
Archived
```

Untuk MVP:

```text
Draft
Published
```

sudah cukup.

---

## 55. Blog Publishing Flow

```text
Admin/Author
    ↓
Create Post
    ↓
Write Content
    ↓
Select Category
    ↓
Add Tags
    ↓
Upload Image
    ↓
Save Draft
    ↓
Preview
    ↓
Publish
    ↓
Public Blog
```

---

## 56. Learning Creation Flow

```text
Admin/Author
    ↓
Create Learning Path
    ↓
Add Description
    ↓
Set Difficulty
    ↓
Create Lessons
    ↓
Arrange Lesson Order
    ↓
Write Content
    ↓
Preview
    ↓
Publish
```

---

## 57. Visitor Learning Flow

Business process utama:

```text
Visitor
   ↓
Open Learn
   ↓
Choose Learning Path
   ↓
Read Overview
   ↓
Start Learning
   ↓
Lesson 1
   ↓
Next Lesson
   ↓
Lesson 2
   ↓
...
   ↓
Complete Learning Path
```

Login tidak diperlukan hanya untuk membaca materi.

Pada fase berikutnya:

```text
Visitor
   ↓
Register/Login
   ↓
Start Learning
   ↓
Complete Lesson
   ↓
Progress Saved
```

---

## 58. Blog-to-Learning Relationship

Artikel dan learning path tidak boleh menjadi dunia yang terpisah.

Contoh:

```text
Learning Path:
PHP untuk Pemula

Lesson:
Variables

Related Articles:
- Memahami Variable PHP
- PHP Data Types
- Kesalahan Umum Pemula PHP
```

---

## 59. Project-to-Blog Relationship

Project juga dapat terhubung ke artikel.

Contoh:

```text
Project:
SIM-PKL

Related Articles:
- Membuat Authentication Laravel
- Laravel Service Layer
- Implementasi Role Permission
- Optimasi Query Laravel
```

Dengan demikian project portfolio juga menjadi sumber pembelajaran.

---

## 60. Error Handling

Website harus memiliki:

```text
404
403
419
429
500
```

Halaman error harus mengikuti design system website.

---

## 61. Empty States

Setiap halaman list harus memiliki empty state.

Contoh:

```text
No articles found.

Try another keyword or category.
```

---

## 62. Pagination

Gunakan Laravel pagination untuk:

```text
Blog
Projects
Admin Posts
Admin Projects
Admin Users
Contact Messages
```

Jangan mengambil seluruh data sekaligus.

---

## 63. Testing Strategy

### Feature Tests

```text
Authentication
Authorization
Post CRUD
Project CRUD
Learning Path CRUD
Lesson CRUD
Contact Form
Public Blog
Public Learning
```

### Unit Tests

Untuk business logic yang memiliki kompleksitas.

### Authorization Tests

Pastikan:

```text
Unauthorized user → 403
Author → hanya resource yang diizinkan
Admin → full access
```

---

## 64. Code Quality

Project harus mengikuti:

- PSR standards;
- Laravel conventions;
- meaningful naming;
- single responsibility;
- reusable components;
- no duplicated business logic;
- no unnecessary abstraction.

---

## 65. Git Strategy

Repository menggunakan Git.

Branch convention:

```text
main
develop
feature/*
fix/*
refactor/*
```

Commit harus jelas.

Contoh:

```text
feat: add blog post management
feat: add learning path module
fix: validate project image upload
refactor: extract post publishing service
```

---

## 66. Environment

Environment configuration menggunakan:

```text
.env
.env.example
```

Secret tidak boleh masuk repository.

`.env` wajib berada di `.gitignore`.

---

## 67. README Requirements

README harus menjelaskan:

```text
Project Overview
Features
Tech Stack
Requirements
Installation
Environment Setup
Database Setup
Migration
Seeder
Running Development Server
Testing
Build Assets
Deployment
```

---

## 68. Seeders

Development harus memiliki sample data.

Seeder minimal:

```text
Admin User
Roles
Permissions
Categories
Tags
Technologies
Sample Posts
Sample Projects
Sample Learning Path
Sample Lessons
```

Credential tidak boleh hard-coded untuk production.

---

## 69. Recommended Laravel Packages

Core:

```text
laravel/framework
laravel/breeze
spatie/laravel-permission
```

Development/testing mengikuti versi Laravel yang digunakan.

Package tambahan tidak boleh dipasang hanya karena terlihat menarik.

Setiap dependency baru harus mempunyai alasan yang jelas.

---

## 70. Package Philosophy

Prinsip:

> **Use Laravel's native capability first.**

Contoh:

```text
Authentication → Breeze
Authorization → Spatie Permission
Validation → Form Request
Authorization → Policy
ORM → Eloquent
File Storage → Laravel Storage
Pagination → Laravel Pagination
Caching → Laravel Cache
Queue → Laravel Queue
Mail → Laravel Mail
```

Jangan menambahkan package untuk fitur yang sudah ditangani Laravel dengan baik.

---

## 71. Content Philosophy

Konten harus:

- beginner-friendly;
- practical;
- structured;
- readable;
- based on real development experience;
- memiliki contoh;
- memiliki code;
- menjelaskan "why", bukan hanya "how".

Tutorial sebaiknya mengikuti:

```text
Concept
↓
Why
↓
Example
↓
Implementation
↓
Common mistake
↓
Practice
```

---

## 72. Beginner Learning Philosophy

Learning path harus membawa pengguna dari:

```text
"I don't understand this."
```

menjadi:

```text
"I understand the concept."
```

kemudian:

```text
"I can implement it."
```

dan akhirnya:

```text
"I can build something with it."
```

---

## 73. Example Learning Path

```text
PHP Beginner
│
├── 01 What is PHP?
├── 02 Installing PHP
├── 03 Hello World
├── 04 Variables
├── 05 Data Types
├── 06 Operators
├── 07 Conditions
├── 08 Loops
├── 09 Functions
├── 10 Arrays
├── 11 OOP
└── 12 Mini Project
```

Kemudian:

```text
PHP Beginner
      ↓
MySQL Beginner
      ↓
Git & GitHub
      ↓
Laravel Beginner
      ↓
Build Laravel Project
```

---

## 74. Future Learning Roadmap

Website dapat berkembang menjadi:

```text
Programming Fundamentals
        ↓
HTML & CSS
        ↓
JavaScript
        ↓
Git & GitHub
        ↓
PHP
        ↓
MySQL
        ↓
Laravel
        ↓
Web Application
        ↓
Deployment
```

---

## 75. Deployment Requirements

Production environment harus mendukung:

```text
PHP
Composer
MySQL
Node/build assets
Web server
HTTPS
Storage
Environment variables
```

Production checklist:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=...
Database configured
Storage linked
Assets built
Migration executed
Cache optimized
```

---

## 76. Observability

MVP minimal menggunakan:

```text
Laravel logs
Error logging
Application logs
```

Tidak perlu langsung menggunakan monitoring infrastructure yang kompleks.

---

## 77. Recommended Implementation Order

AI coding agent tidak boleh langsung membangun seluruh sistem sekaligus.

```text
PHASE 0
Project Audit & Planning
        ↓
PHASE 1
Laravel Foundation
        ↓
PHASE 2
Authentication & Authorization
        ↓
PHASE 3
Public Design System
        ↓
PHASE 4
Portfolio
        ↓
PHASE 5
Blog CMS
        ↓
PHASE 6
Learning Platform
        ↓
PHASE 7
SEO & Performance
        ↓
PHASE 8
Testing & Hardening
        ↓
PHASE 9
Deployment
```

---

## 78. AI Coding Agent Rules

### Rule 1 — Inspect Before Editing

Sebelum melakukan perubahan:

```text
Inspect existing project
Inspect composer.json
Inspect package.json
Inspect routes
Inspect models
Inspect migrations
Inspect views
Inspect configuration
```

Jangan mengasumsikan struktur project.

### Rule 2 — Plan Before Implementation

Sebelum implementasi besar:

1. Jelaskan tujuan.
2. Identifikasi file yang akan berubah.
3. Identifikasi dependency.
4. Identifikasi database changes.
5. Identifikasi risiko.
6. Tunggu approval jika perubahan bersifat architectural.

### Rule 3 — Small Changes

Implementasi dilakukan secara incremental.

Jangan mengubah authentication, database, frontend, admin, blog, dan learning sekaligus dalam satu perubahan besar.

### Rule 4 — Preserve Existing Functionality

Sebelum perubahan besar:

```text
Run existing tests
```

Setelah perubahan:

```text
Run relevant tests
Run full test suite
```

---

## 79. MVP Scope

MVP wajib mencakup:

### Foundation

- Laravel;
- Blade;
- Vite;
- Tailwind;
- Breeze;
- Spatie Permission;
- MySQL;
- migrations;
- seeders;
- authentication;
- authorization.

### Portfolio

- Home;
- About;
- Skills;
- Projects;
- Project detail;
- Contact.

### Blog

- Posts;
- Categories;
- Tags;
- Search;
- Post detail;
- Draft/published;
- SEO metadata.

### Learning

- Learning paths;
- Lessons;
- Lesson ordering;
- Lesson navigation;
- Code blocks;
- Learning overview.

### Admin

- Dashboard;
- Posts;
- Categories;
- Tags;
- Projects;
- Technologies;
- Learning Paths;
- Lessons;
- Users;
- Roles/permissions.

---

## 80. Phase 2

Setelah MVP stabil:

```text
User profile
Bookmark
Learning progress
Completed lessons
Related content
Advanced search
Analytics
```

---

## 81. Phase 3

Fitur lanjutan:

```text
Comments
Likes
Discussion
Notifications
Content recommendations
SEO improvements
Newsletter
RSS
```

---

## 82. Phase 4

Jika platform berkembang:

```text
Course system
Quizzes
Assignments
Certificates
User dashboard
Learning analytics
Contributor system
```

Fitur Phase 4 tidak boleh mempengaruhi kompleksitas MVP.

---

## 83. Definition of Done — Foundation

Foundation dianggap selesai jika:

- [ ] Laravel berjalan;
- [ ] database terkoneksi;
- [ ] Breeze berjalan;
- [ ] login/logout berjalan;
- [ ] Spatie Permission terpasang;
- [ ] role admin tersedia;
- [ ] permission tersedia;
- [ ] middleware authorization bekerja;
- [ ] Blade layout tersedia;
- [ ] Vite berjalan;
- [ ] Tailwind berjalan;
- [ ] Git repository bersih;
- [ ] README tersedia.

---

## 84. Definition of Done — Portfolio

Portfolio selesai jika:

- [ ] Home responsive;
- [ ] About tersedia;
- [ ] Skills tersedia;
- [ ] Project listing tersedia;
- [ ] Project detail tersedia;
- [ ] project CRUD tersedia di admin;
- [ ] image upload bekerja;
- [ ] repository/live URL bekerja;
- [ ] SEO dasar tersedia.

---

## 85. Definition of Done — Blog

Blog selesai jika:

- [ ] admin dapat membuat post;
- [ ] admin dapat edit post;
- [ ] admin dapat delete/archive post;
- [ ] post dapat menjadi draft;
- [ ] post dapat dipublish;
- [ ] category tersedia;
- [ ] tag tersedia;
- [ ] search bekerja;
- [ ] pagination bekerja;
- [ ] article detail tersedia;
- [ ] syntax highlighting bekerja;
- [ ] SEO metadata tersedia.

---

## 86. Definition of Done — Learning

Learning selesai jika:

- [ ] admin dapat membuat learning path;
- [ ] admin dapat membuat lesson;
- [ ] lesson dapat diurutkan;
- [ ] learning path dapat dipublish;
- [ ] visitor dapat melihat learning path;
- [ ] visitor dapat membuka lesson;
- [ ] previous/next navigation bekerja;
- [ ] code block bekerja;
- [ ] responsive pada mobile.

---

## 87. Definition of Done — Security

Sistem dianggap aman untuk MVP jika:

- [ ] unauthorized request ditolak;
- [ ] authorization dilakukan server-side;
- [ ] Form Request digunakan;
- [ ] CSRF aktif;
- [ ] password di-hash;
- [ ] upload divalidasi;
- [ ] mass assignment dikontrol;
- [ ] production tidak menampilkan debug information;
- [ ] secret tidak berada di Git.

---

## 88. Final Product Structure

```text
RANDZ.DEV
│
├── HOME
│
├── ABOUT
│   ├── Profile
│   ├── Journey
│   └── Skills
│
├── PROJECTS
│   ├── Project List
│   └── Project Case Study
│
├── BLOG
│   ├── Articles
│   ├── Tutorials
│   ├── Categories
│   ├── Tags
│   └── Search
│
├── LEARN
│   ├── Learning Paths
│   └── Lessons
│
├── CONTACT
│
└── ADMIN
    ├── Dashboard
    ├── Posts
    ├── Categories
    ├── Tags
    ├── Projects
    ├── Technologies
    ├── Learning Paths
    ├── Lessons
    ├── Users
    ├── Roles
    ├── Permissions
    └── Settings
```

---

## 89. Final Technical Stack

```text
Backend
Laravel

Frontend
Blade
Tailwind CSS
Vanilla JavaScript
Vite

Authentication
Laravel Breeze

Authorization
Spatie Laravel Permission

Database
MySQL

ORM
Eloquent

Validation
Laravel Form Requests

Authorization
Policies + Permissions

Templating
Blade Components

Testing
Pest / PHPUnit

Version Control
Git + GitHub

Storage
Laravel Filesystem

SEO
Server-rendered Blade + SEO metadata

Architecture
Laravel Monolith / MVC
```

---

## 90. MVP Priorities

```text
P0 — Critical

Laravel Foundation
Authentication
Authorization
Admin
Portfolio
Blog
Learning Path
Lessons
Responsive UI
Security

P1 — Important

Search
SEO
Code Highlighting
Image Upload
Draft/Publish
Categories
Tags
Technologies

P2 — Future

Learning Progress
Bookmark
User Profile
Comments
Analytics

P3 — Advanced

Quiz
Certificate
Discussion
Notifications
Contributor Platform
```

---

## 91. Final Product Philosophy

Website ini bukan hanya:

> "Portfolio saya."

Tetapi:

> **"Tempat saya menunjukkan apa yang saya bangun, mendokumentasikan apa yang saya pelajari, dan membantu orang lain belajar programming."**

Semua fitur harus mendukung tiga hal tersebut:

```text
BUILD
  ↓
PROJECTS

LEARN
  ↓
BLOG + TUTORIALS

SHARE
  ↓
LEARNING PATHS
```

Itulah core identity dari Randz.dev.
