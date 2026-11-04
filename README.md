
# MooShop - Shop Management System

ระบบจัดการสินค้าสำหรับร้านค้า พัฒนาด้วย PHP, MySQL และ Docker Compose

## Features

- Dashboard แสดงภาพรวมข้อมูลสินค้า
- เพิ่มสินค้า (Create)
- แสดงรายการสินค้า (Read)
- แก้ไขสินค้า (Update)
- ลบสินค้า (Delete)
- ค้นหาสินค้า
- แสดงจำนวนสินค้าคงเหลือ
- จัดการฐานข้อมูลผ่าน phpMyAdmin
- เก็บข้อมูลถาวรด้วย Docker Volume

## Technologies

- PHP 8.3
- Apache Web Server
- MySQL 8.4
- phpMyAdmin
- HTML, CSS, JavaScript
- Docker และ Docker Compose

## Database Structure

ฐานข้อมูลชื่อ `shop_db` ประกอบด้วย 4 ตาราง

| Table | Description |
|---|---|
| categories | เก็บหมวดหมู่สินค้า |
| products | เก็บข้อมูลสินค้า |
| orders | เก็บข้อมูลคำสั่งซื้อ |
| order_items | เก็บรายละเอียดสินค้าในคำสั่งซื้อ |

### Relationships

- categories (1) → (N) products
- products (1) → (N) order_items
- orders (1) → (N) order_items

ระบบมีข้อมูลตัวอย่างสำหรับทดสอบ

## Project Structure

```text
shop-management/
├── database/
│   └── init.sql
├── web/
│   ├── Dockerfile
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   ├── delete.php
│   ├── db.php
│   └── style.css
├── docker-compose.yml
├── .env.example
├── .gitignore
└── README.md
```

## Installation

### 1. Clone Repository

```bash
git clone YOUR_REPOSITORY_URL
cd shop-management
```

### 2. Configure Environment

```bash
cp .env.example .env
```

แก้ไขไฟล์ `.env` เพื่อกำหนดรหัสผ่านฐานข้อมูล

### 3. Start Docker Containers

```bash
docker compose up -d --build
```

### 4. Access Application

| Service | URL |
|---|---|
| MooShop Web | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |

phpMyAdmin ใช้ชื่อผู้ใช้ `root` และรหัสผ่านตามไฟล์ `.env`

กรณีใช้งานผ่าน Ubuntu VirtualBox แบบ NAT ต้องตั้งค่า Port Forwarding สำหรับ Port 8080 และ 8081 เพื่อเปิดเว็บจาก Windows

## Stop Application

```bash
docker compose down
```

คำสั่งนี้หยุดระบบโดยยังเก็บข้อมูลใน Docker Volume

**Warning:** `docker compose down -v` จะลบ Volume และข้อมูลในฐานข้อมูล

## CRUD Demonstration

1. เปิดหน้า Dashboard
2. ตรวจสอบรายการสินค้า
3. เพิ่มสินค้าใหม่
4. แก้ไขราคาหรือจำนวนสินค้า
5. ลบสินค้าทดสอบ
6. ตรวจสอบข้อมูลผ่าน phpMyAdmin

## Author

Student Project - Shop Management System
