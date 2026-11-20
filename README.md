# Aplikasi Akademik (PHP MVC) — Lecturers, Departments, Courses

## 1. Cara Menjalankan

1. Salin folder ini ke `htdocs` (XAMPP) atau document root web server.
2. Impor `tp_mvc25.sql` ke MySQL (database `tp_mvc25`).
3. Sesuaikan kredensial database di `config.php` bila perlu.
4. Buka `http://localhost/mvc_app/` (halaman Lecturers).

Kebutuhan: PHP 8.1+, ekstensi `mysqli` (mysqlnd), MySQL/MariaDB.

## 2. Struktur Direktori

```
mvc_app/
├── controllers/
│   ├── BaseController.php        # logika alur CRUD (dipakai bersama)
│   ├── LecturerController.php
│   ├── DepartmentController.php
│   └── CourseController.php
├── models/
│   ├── DB.php                    # koneksi + CRUD generik (base model)
│   ├── Lecturer.php
│   ├── Department.php
│   └── Course.php
├── views/
│   ├── Template.php              # mesin template (ganti placeholder DATA_xxx)
│   ├── LecturerView.php
│   ├── DepartmentView.php
│   └── CourseView.php
├── templates/                    # file HTML murni dengan placeholder
│   ├── layout.html               # kerangka halaman (navbar, alert, bootstrap)
│   ├── index.html                # form + tabel Lecturers
│   ├── department.html           # form + tabel Departments
│   └── course.html               # form + tabel Courses
├── assets/                       # bootstrap css & js
├── config.php                    # konfigurasi DB + autoload class
├── index.php                     # entry point Lecturers
├── department.php                # entry point Departments
├── course.php                    # entry point Courses
├── tp_mvc25.sql                  # struktur database
└── README.md
```

## 3. Desain Program

### 3.1 Desain Database (ERD)

```
 departments 1 ───────< lecturers 1 ───────< courses
 ┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
 │ id (PK)          │    │ id (PK)          │    │ id (PK)          │
 │ department_name  │    │ name             │    │ course_code (UQ) │
 │ faculty          │    │ nidn (UQ)        │    │ course_name      │
 │ office_location  │    │ email            │    │ credits          │
 └──────────────────┘    │ phone            │    │ semester         │
                         │ join_date        │    │ lecturer_id (FK) │
                         │ department_id(FK)│    └──────────────────┘
                         └──────────────────┘
```

| Relasi | Keterangan |
|---|---|
| `lecturers.department_id → departments.id` | Satu department punya banyak dosen. `ON DELETE SET NULL` |
| `courses.lecturer_id → lecturers.id` | Satu dosen mengampu banyak mata kuliah. `ON DELETE SET NULL` |

`lecturers` adalah tabel awal; `departments` dan `courses` adalah tabel tambahan yang berelasi dengannya.

### 3.2 Desain Kelas (OOP)

```
                 DB (models/DB.php)
   koneksi mysqli + run(), select(), all(), find(), create(), update(), delete()
        ▲              ▲                ▲
   Lecturer       Department         Course
   ($table,$fields; override all() untuk JOIN)

            BaseController (controllers/BaseController.php)
   handle(), handlePost(), collect(), flash(), redirect()
        ▲                  ▲                    ▲
 LecturerController  DepartmentController  CourseController
 (set model, view, page, label, nullable, extraData())

 LecturerView / DepartmentView / CourseView  ──memakai──►  Template
 render($rows, $edit, $extra, $flash)                       set(), write(), tr(), actions()
```

### 3.3 Pembagian Tanggung Jawab MVC

| Layer | File | Tugas |
|---|---|---|
| **Model** | `models/*.php` | Satu-satunya yang berbicara dengan database (query prepared statement). Tidak tahu HTML. |
| **View** | `views/*View.php`, `Template.php`, `templates/*.html` | Menyusun HTML dari data. Tidak menyentuh database. |
| **Controller** | `controllers/*.php` | Membaca request (`$_GET`/`$_POST`), memanggil Model, memilih View. |
| **Entry point** | `index.php`, `department.php`, `course.php` | Memuat `config.php` lalu membuat satu controller dan menjalankan `handle()`. |

### 3.4 Sistem Template

File di `templates/` adalah HTML biasa yang berisi **placeholder** berawalan `DATA_`.
View mengisi placeholder lewat `$template->set('DATA_NAMA', $nilai)`, lalu `write()` mencetak hasilnya
ke dalam `layout.html`.

| Placeholder | Diisi dengan |
|---|---|
| `DATA_TITLE`, `DATA_ALERT`, `DATA_CONTENT` | Judul halaman, pesan sukses/gagal, isi template (di `layout.html`) |
| `DATA_FORM_TITLE`, `DATA_HEADER_CLASS` | Judul form dan warna header (biru = tambah, kuning = ubah) |
| `DATA_ACTION`, `DATA_ID` | Nilai hidden field: `add` / `update` dan id data |
| `DATA_NAME`, `DATA_NIDN`, ... | Nilai awal tiap input (kosong saat tambah, terisi saat ubah) |
| `DATA_*_OPTIONS` | Daftar `<option>` dropdown relasi (department / lecturer) |
| `DATA_CANCEL` | Tombol Cancel (hanya muncul saat mode ubah) |
| `DATA_TABEL` | Baris `<tr>` hasil query |

Semua nilai dari database/input di-escape dengan `Template::e()` (cegah XSS).

## 4. Penjelasan Alur

Setiap halaman memiliki **satu URL** yang menampilkan form dan tabel sekaligus.
Aksi ditentukan oleh method HTTP dan field `action`.

### 4.1 Alur Umum Sebuah Request

```
Browser ──► index.php ──► config.php (konstanta DB + autoload)
                │
                ▼
        new LecturerController()   ← menyiapkan Model, View, page, label
                │  handle()
                ▼
     method POST?  ── ya ──► handlePost()   (lihat 4.3)
                │
               tidak
                ▼
        GET: ambil data lewat Model ──► View::render() ──► HTML ke browser
```

### 4.2 Alur Menampilkan Data (GET)

1. Browser membuka `index.php`.
2. `LecturerController::handle()` mendeteksi method `GET`.
3. Controller meminta data ke Model: `Lecturer::all()` (JOIN dengan `departments`) dan `extraData()` (daftar department untuk dropdown).
4. Bila ada `?edit=ID`, controller memanggil `Lecturer::find(ID)` agar form terisi data lama.
5. Controller memanggil `LecturerView::render(...)`.
6. View memuat `templates/index.html`, mengisi placeholder, dibungkus `layout.html`, lalu dicetak ke browser.

### 4.3 Alur Tambah / Ubah / Hapus (POST)

Form mengirim `action` = `add`, `update`, atau `delete` (hapus lewat form kecil dengan konfirmasi JavaScript).

| `action` | Proses di controller | Pesan |
|---|---|---|
| `add` | `collect()` ambil field milik model → `Model::create()` | "... berhasil ditambahkan." |
| `update` | `collect()` → `Model::update($id, ...)` | "... berhasil diubah." |
| `delete` | `Model::delete($id)` | "... berhasil dihapus." |

Setelah sukses, controller menyimpan pesan di `$_SESSION['flash']` lalu **redirect ke halaman yang sama**
(pola *Post-Redirect-Get*) supaya refresh browser tidak mengirim ulang form.
Pada request GET berikutnya pesan dibaca sekali lalu dihapus dan ditampilkan sebagai alert.

**Jika gagal** (misalnya NIDN / kode course duplikat, error MySQL 1062), controller **tidak redirect**;
form ditampilkan kembali dengan data yang tadi diisi beserta pesan error, sehingga pengguna tidak perlu mengetik ulang.

### 4.4 Contoh Alur Lengkap: Menambah Course

```
1. Buka course.php                → CourseController → Course::all() + Lecturer::all()
                                     → CourseView → course.html → form kosong + tabel
2. Isi form, klik Submit          → POST course.php  (action=add, course_code=..., lecturer_id=3)
3. BaseController::handlePost()   → collect(): lecturer_id kosong diubah jadi NULL
                                     → Course::create($data)  (INSERT prepared statement)
4. Sukses                         → flash "Course berhasil ditambahkan." → redirect course.php
5. GET course.php                 → tabel baru tampil + alert hijau
```

### 4.5 Alur Edit

```
Klik "Edit" (link course.php?edit=5)
   → GET: Course::find(5) → View mengisi form (DATA_ACTION = update, DATA_ID = 5, header kuning)
   → Submit → POST action=update&id=5 → Course::update(5, ...) → redirect
```

## 5. Catatan Teknis

- **Keamanan**: seluruh query memakai *prepared statement* (anti SQL injection), output di-escape (anti XSS), dan penghapusan memakai POST (bukan link GET).
- **Nilai NULL**: kolom relasi (`department_id`, `lecturer_id`) boleh kosong; controller mengubah string kosong menjadi `NULL` lewat `$nullable`.
- **Menambah tabel baru**: buat satu model (turunan `DB`), satu view, satu controller (turunan `BaseController`), satu template HTML, dan satu file entry point.
- **Penyesuaian dari kode awal**: kolom `email` dan `department_id` pada `lecturers` kini ikut digunakan di form; path redirect yang salah (`/bakekok/`, `crud100/`) tidak dipakai lagi.

## Screen record
https://drive.google.com/file/d/18eFq1N0HyIacUtlEEyuBBe4h28WnAkDm/view?usp=sharing