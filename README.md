# P2P Cryptocurrency Exchange (Backend API)

โปรเจคนี้คือระบบ Backend สำหรับจำลองการแลกเปลี่ยน Cryptocurrency แบบ P2P พัฒนาขึ้นด้วย **Laravel** ตอบสนองโจทย์การออกแบบฐานข้อมูลและการสร้าง API พื้นฐาน

## ฟีเจอร์หลัก (Features)
- สร้างบัญชีผู้ใช้และกระเป๋าเงิน (Fiat & Crypto)
- ตั้งประกาศซื้อ/ขาย Cryptocurrencies แบบ P2P (BTC, ETH, XRP, DOGE) ด้วยเงิน Fiat (THB, USD)
- ระบบบันทึกการจับคู่ซื้อ-ขาย (P2P Trades)
- ระบบบันทึกประวัติการฝาก/ถอน/โอนภายในระบบ (Transactions)

## โครงสร้างระบบ (System Architecture)
- **ER Diagram:** สามารถดูโครงสร้างความสัมพันธ์ของตารางทั้งหมดในรูปแบบตัวอักษรทาง `er-diagram.md` (ใช้โค้ดชุดนี้ไปวางในเครื่องมือ [Mermaid Live Editor](https://mermaid.live/) เพื่อดูภาพได้เลย)
- **Models & Relationships:** กำหนดแบบ One-to-Many ไว้ครบถ้วน (ตัวอย่างเช่นระหว่าง User กับ Wallet, Order, Trade และ Transaction)
- **Seeders:** มีการจำลองข้อมูลทุกตารางเพื่อให้สามารถทดสอบได้ทันที

## ขั้นตอนการติดตั้งและรันโปรเจค (How to Run)

**ความต้องการของระบบ (Prerequisites):** 
- PHP >= 8.2
- Composer
- ฐานข้อมูล SQLite (หรือสลับไปใช้ MySQL ตามถนัดผ่านไฟล์ `.env`)

**ขั้นตอน:**
1. ติดตั้ง Dependencies ทั้งหมด
   ```bash
   composer install
   ```

2. สร้างไฟล์ `.env` (หากยังไม่มี สามารถก็อปปี้จาก `.env.example` ได้เลย)
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. สร้างฐานข้อมูลและดึงข้อมูลจำลอง (Migrate & Seed)
   ```bash
   php artisan migrate:fresh --seed
   ```
   > คำสั่งนี้จะสร้าง Migration จำนวน 5 ตาราง และ Seed ข้อมูลผู้ใช้งาน 5 คน, สร้างกระเป๋า 6 สกุลเงิน(THB, USD, BTC, ETH, XRP, DOGE) และจำลองการตั้งประกาศ/เทรดเบื้องต้นเข้าสู่ฐานข้อมูล

4. เปิดใช้งาน Server
   ```bash
   php artisan serve
   ```
   *เซิร์ฟเวอร์จะรันที่ `http://127.0.0.1:8000`*

## การทดสอบ API (Testing the API)

หลังจากรันคำสั่ง `php artisan serve` สำเร็จ คุณสามารถใช้เครื่องมือ อย่าง **Postman**, **REST Client** หรือ **cURL** เพื่อทดสอบ API 

**Endpoints หลักๆ (ลองยิงด้วย GET):**
- ดูรายชื่อ User ทั้งหมด: `GET http://127.0.0.1:8000/api/users`
- ดูกระเป๋าเงินของ User Id = 1: `GET http://127.0.0.1:8000/api/users/1/wallets`
- ดูประกาศซื้อขายคริปโตที่เปิดใช้งานอยู่: `GET http://127.0.0.1:8000/api/p2p-orders`
- ดูรายการ Transaction ของ User Id = 1: `GET http://127.0.0.1:8000/api/users/1/transactions`

**(รายการ Endpoints ฉบับเต็ม ตรวจสอบได้ด้วยคำสั่ง `php artisan route:list`)**
