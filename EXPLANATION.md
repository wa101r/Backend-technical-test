# อธิบายระบบ P2P Crypto Exchange ฉบับอ่านเอง

## ภาพรวมของระบบ (System Overview)

ระบบนี้จำลองการทำงานของ **ศูนย์กลางแลกเปลี่ยน Cryptocurrency แบบ P2P** (Peer-to-Peer) คล้ายกับ Binance P2P
หลักการคือ **ผู้ใช้สามารถตั้งประกาศซื้อหรือขายเหรียญ Crypto ได้เอง** แล้วผู้ใช้คนอื่นเข้ามาจับคู่ซื้อขายกัน

---

## ตารางในฐานข้อมูล (5 ตาราง)

### 1. `users` — ผู้ใช้งาน
เก็บข้อมูลผู้ใช้ที่สมัครเข้ามาในระบบ
- มี `username`, `email`, `password`
- **1 User มีได้หลาย Wallet, หลาย Order, หลาย Trade, หลาย Transaction**

### 2. `wallets` — กระเป๋าเงิน
ผู้ใช้ 1 คนจะมี **6 กระเป๋า** (THB, USD, BTC, ETH, XRP, DOGE)
- กระเป๋า Fiat (THB, USD) = เงินจริงที่ใช้ซื้อเหรียญ
- กระเป๋า Crypto (BTC, ETH, XRP, DOGE) = เหรียญ Crypto ที่ถือ
- ฟิลด์ `balance` เก็บยอดเงินคงเหลือ

### 3. `p2p_orders` — ประกาศซื้อ/ขาย (กระดาน P2P)
เมื่อผู้ใช้อยากขาย BTC ก็จะ **ตั้งประกาศ** บอกว่า:
> "ผมจะขาย BTC จำนวน 0.5 เหรียญ ในราคา 1,500,000 บาทต่อเหรียญ"

- `type` = BUY หรือ SELL
- `price` = ราคาต่อเหรียญ (Fiat/Crypto)
- `total_amount` = จำนวนเหรียญทั้งหมดที่ตั้งไว้
- `remaining_amount` = จำนวนเหรียญที่เหลือ (ลดลงทุกครั้งที่มีคนมาซื้อ)
- `status` = OPEN (เปิดอยู่) / COMPLETED (ขายหมดแล้ว) / CANCELLED (ยกเลิก)

### 4. `p2p_trades` — การจับคู่ซื้อขาย
เมื่อมีคนเข้ามากด "ซื้อ" จากประกาศ จะเกิดรายการ Trade ขึ้น
- `order_id` = อ้างอิงว่ามาจากประกาศไหน
- `buyer_id` = ใครเป็นคนซื้อ
- `seller_id` = ใครเป็นคนขาย
- `crypto_amount` = ซื้อกี่เหรียญ
- `fiat_amount` = ต้องจ่ายเงินเท่าไหร่ (คำนวณจาก crypto_amount × price)
- `status` = PENDING → PAID → RELEASED (สำเร็จ) หรือ CANCELLED

**ตัวอย่าง Flow:**
1. User A ตั้งประกาศขาย BTC 0.5 เหรียญ ราคา 1.5M THB (Order เกิดขึ้น)
2. User B เข้ามากดซื้อ 0.1 BTC (Trade เกิดขึ้น, fiat_amount = 150,000 THB)
3. User B โอนเงิน THB ให้ User A แล้วกด "PAID"
4. User A ยืนยันว่าได้เงินแล้ว กด "RELEASED" → ระบบโอน BTC ให้ User B
5. Order เหลือ remaining_amount = 0.4 BTC

### 5. `transactions` — ประวัติการโอนเงิน/เหรียญ
เก็บประวัติทุกครั้งที่มีการฝาก/ถอน/โอนเงินหรือเหรียญ
- `type`:
  - `DEPOSIT` = ฝากเงินเข้ากระเป๋า
  - `WITHDRAW` = ถอนเงินออก (โอนไปข้างนอกระบบ)
  - `TRANSFER_INTERNAL` = โอนหากันภายในระบบ
- `to_user_id` = โอนให้ใคร (กรณีโอนภายในระบบ)
- `to_address` = ที่อยู่กระเป๋าปลายทาง (กรณีโอนออกข้างนอก เช่น Blockchain address)

---

## ความสัมพันธ์ (Relationships)

```
User ──┬── hasMany ──→ Wallet        (1 คนมีหลายกระเป๋า)
       ├── hasMany ──→ P2pOrder      (1 คนตั้งประกาศได้หลายตัว)
       ├── hasMany ──→ P2pTrade      (1 คนเทรดได้หลายครั้ง ทั้งเป็นคนซื้อและคนขาย)
       └── hasMany ──→ Transaction   (1 คนมีประวัติธุรกรรมหลายรายการ)

P2pOrder ── hasMany ──→ P2pTrade     (1 ประกาศถูกแบ่งซื้อได้หลายครั้ง)
Wallet ──── hasMany ──→ Transaction  (1 กระเป๋ามีธุรกรรมหลายรายการ)
```

---

## โครงสร้างไฟล์สำคัญ

```
app/
├── Models/
│   ├── User.php          ← ผู้ใช้ (มี wallets(), p2pOrders(), buyTrades(), sellTrades(), transactions())
│   ├── Wallet.php        ← กระเป๋า (มี user(), transactions())
│   ├── P2pOrder.php      ← ประกาศ (มี user(), trades())
│   ├── P2pTrade.php      ← การเทรด (มี order(), buyer(), seller())
│   └── Transaction.php   ← ธุรกรรม (มี user(), wallet(), toUser())
│
├── Http/Controllers/
│   ├── UserController.php         ← สมัครสมาชิก, ดูข้อมูลผู้ใช้
│   ├── WalletController.php       ← ดูกระเป๋าเงิน
│   ├── P2pOrderController.php     ← ตั้งประกาศซื้อ/ขาย
│   ├── P2pTradeController.php     ← จับคู่เทรด, อัพเดทสถานะ
│   └── TransactionController.php  ← ฝาก/ถอน/โอนเงิน
│
database/
├── migrations/            ← สร้างตารางฐานข้อมูล
├── seeders/
│   ├── UserSeeder.php     ← สร้าง 5 users + กระเป๋า 6 สกุลเงินต่อคน
│   ├── P2pOrderSeeder.php ← ตัวอย่างประกาศซื้อ/ขาย
│   ├── P2pTradeSeeder.php ← ตัวอย่างการเทรด
│   └── TransactionSeeder.php ← ตัวอย่างธุรกรรม
│
routes/
└── api.php                ← API Endpoints ทั้งหมด
```

---

## Checklist ตรวจสอบกับโจทย์

| โจทย์ | สถานะ | ไฟล์ที่เกี่ยวข้อง |
|---|---|---|
| **ข้อ 1: ER Diagram** | ✅ ครบ | `er-diagram.md` |
| ├ ตั้งซื้อ-ขาย BTC, ETH, XRP, DOGE | ✅ | `p2p_orders` (enum: BTC,ETH,XRP,DOGE) |
| ├ บันทึกการโอนและซื้อขาย | ✅ | `transactions` + `p2p_trades` |
| └ สร้างบัญชีผู้ใช้ | ✅ | `users` + `wallets` |
| **ข้อ 2: เขียนด้วย PHP (Laravel)** | ✅ ครบ | |
| ├ Model Relationships (One-to-Many) | ✅ | `app/Models/*.php` |
| ├ Controller & Routing หลักๆ | ✅ | `app/Http/Controllers/*.php` + `routes/api.php` |
| └ Seed ข้อมูลทดสอบ | ✅ | `database/seeders/*.php` |
| **README.md ขั้นตอนการรัน** | ✅ | `README.md` |
| **ส่งผ่าน Github** | ✅ | Push แล้ว |
