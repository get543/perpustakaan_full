# Sistem Perpustakaan Modern (Native PHP)

Struktur:
- 3 role: Admin, Petugas (Librarian), Member
- Folder terpisah untuk admin, librarian, dan member
- Fitur:
  - CRUD buku (Librarian)
  - Booking buku oleh member
  - Konfirmasi booking & peminjaman oleh librarian
  - Penghitungan denda terlambat pengembalian

# Struktur Folder
```txt
perpustakaan_full/
├── admin/  
│   ├── dashboard.php
│   ├── reports.php
│   └── users.php
├── assets/
│   ├── css/style.css
│   └── uploads/
├── auth/
│   ├─── login.php
│   └─── logout.php
├───config
│   └─── config.php
├───includes
│   ├─── auth.php
│   ├─── footer.php
│   ├─── header.php
│   └─── navbar.php
├───librarian
│   ├─── bookings.php
│   ├─── books.php
│   ├─── dashboard.php
│   └─── loans.php
├────member
│    ├───book_detail_api.php
│    ├───book_detail.php
│    ├───booking_cancel_api.php
│    ├───booking_create_api.php
│    ├───booking_create.php
│    ├───catalog_api.php
│    ├───catalog.php
│    ├───my_bookings_api.php
│    ├───my_bookings.php
│    ├───my_loans_api.php
│    ├───my_loans.php
│    └───yanto.php
├─── database.sql
├─── index.php
└─── README.md
```

SQL query ada di file database.sql.
