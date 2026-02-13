# SmartOrder - Restaurant Self-Ordering System

A QR code-based self-ordering system for restaurants, built with PHP and MySQL.

**Live Demo:** https://restaurant-project-production-a27b.up.railway.app

---

## Why I Built This

As a frequent izakaya customer, I observed that many restaurants still rely on inefficient ordering processes such as paper menus or poorly designed tablet systems.

These systems often create friction in the customer experience and increase operational workload for staff.

This project was built to redesign the ordering experience from both the customer and business perspectives. The goal was to create a simple, intuitive, and scalable digital ordering system that improves usability while supporting restaurant efficiency.

## なぜ作ったのか

居酒屋によく行く中で、多くの飲食店がまだ紙のメニューや使いにくいタブレットに頼っていることに気づきました。

これらのシステムはお客様の体験に摩擦を生み、スタッフの業務負担を増やしています。

このプロジェクトは、お客様と店舗の両方の視点から注文体験を再設計するために作りました。シンプルで直感的、そしてスケーラブルなデジタル注文システムを目指しています。

---

## How It Works / システムの仕組み

### Customer Flow / お客様の流れ

1. **Scan QR Code** - Each table has a unique QR code placed on it
2. **Browse Menu** - Customers view the menu with categories, images, and prices on their smartphone
3. **Add to Cart** - Select items and adjust quantities
4. **Place Order** - Submit the order directly to the kitchen/staff

### Staff Flow / スタッフの流れ

1. **Management Page** - View all active orders in real-time (auto-refreshes every 10 seconds)
2. **Order Details** - Check individual order contents and total amounts
3. **Process Payment** - Mark orders as paid when customers settle the bill
4. **Admin Panel** - Add, edit, or delete menu items and categories

---

## Tech Stack / 技術スタック

| Category | Technology |
|----------|-----------|
| **Frontend** | HTML, CSS, JavaScript |
| **Backend** | PHP 8.2 |
| **Database** | MySQL 9.4 (Railway) / MariaDB 10.4 (Local) |
| **Deployment** | Railway (PaaS) + Docker |
| **Version Control** | Git / GitHub |
| **QR Code API** | goqr.me API |

---

## Architecture / アーキテクチャ

```
[Customer Smartphone]
        |
    QR Code Scan
        |
        v
[Railway Cloud Server]
   PHP 8.2 (Docker)
        |
        v
  [MySQL Database]
   (Railway MySQL)
        |
        v
[Management Dashboard]
   (Staff Browser)
```

### Deployment on Railway / Railwayでのデプロイ

The application is deployed on **Railway**, a cloud platform that supports automatic deployments from GitHub.

- **Web Service**: PHP 8.2 running in a Docker container with PDO MySQL extension
- **Database**: MySQL 9.4 hosted on Railway with persistent volume storage
- **Auto-Deploy**: Every push to the `main` branch triggers an automatic deployment
- **Environment Variables**: Database credentials are managed through Railway's environment variables (`MYSQLHOST`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLPORT`)

アプリケーションは **Railway** にデプロイされています。GitHubからの自動デプロイに対応したクラウドプラットフォームです。

- **Webサービス**: PDO MySQL拡張を含むDockerコンテナ上のPHP 8.2
- **データベース**: Railway上のMySQL 9.4（永続ボリュームストレージ付き）
- **自動デプロイ**: `main`ブランチへのpushで自動的にデプロイが実行されます
- **環境変数**: データベース接続情報はRailwayの環境変数で管理

---

## Project Structure / プロジェクト構成

```
smartorder/
├── index.php              # Customer ordering page (お客様注文ページ)
├── logic.php              # Menu data API (メニューデータAPI)
├── cart_update.php        # Cart update handler (カート更新)
├── cart_remove.php        # Cart remove handler (カート削除)
├── checkout.php           # Order submission (注文送信)
├── admin.php              # Admin dashboard (管理画面)
├── admin_item_save.php    # Save menu item (商品保存)
├── admin_item_delete.php  # Delete menu item (商品削除)
├── admin_category_save.php    # Save category (カテゴリ保存)
├── admin_category_delete.php  # Delete category (カテゴリ削除)
├── management.php         # Order management (注文管理)
├── process_payment.php    # Payment processing (会計処理)
├── order.php              # Order detail view (注文詳細)
├── qrcode.php             # QR code generator (QRコード生成)
├── login.php              # Admin login (ログイン)
├── logout.php             # Admin logout (ログアウト)
├── auth.php               # Authentication logic (認証ロジック)
├── pdo.php                # Database connection (DB接続)
├── import_db.php          # Database import tool (DBインポート)
├── Dockerfile             # Docker configuration (Docker設定)
├── database_utf8.sql      # Database schema & seed data (DBスキーマ)
└── itemImages/            # Menu item images (商品画像)
```

---

## Database Schema / データベース構成

| Table | Description |
|-------|------------|
| `sCategory` | Menu categories (メニューカテゴリ) |
| `sItem` | Menu items with name, price, category (商品情報) |
| `sManagement` | Order sessions per table (注文セッション) |
| `sOrder` | Individual order items and quantities (注文明細) |

---

## Local Development / ローカル開発

### Requirements / 必要なもの

- XAMPP (PHP 8.x + MariaDB)
- Git

### Setup / セットアップ

1. Clone the repository:
   ```bash
   git clone https://github.com/Z200-WEB/restaurant-project.git
   ```

2. Place in XAMPP htdocs:
   ```
   C:\xampp\htdocs\smartorder\
   ```

3. Import the database:
   - Start XAMPP (Apache + MySQL)
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create database `practice`
   - Import `database_utf8.sql`

4. Access the app:
   - Customer page: http://localhost/smartorder/index.php?tableNo=1
   - Admin panel: http://localhost/smartorder/admin.php

---

## Features / 機能一覧

- QR code-based table ordering (QRコードによるテーブル注文)
- Real-time order management with auto-refresh (リアルタイム注文管理・自動更新)
- Category-based menu display (カテゴリ別メニュー表示)
- Shopping cart with quantity control (数量調整付きカート)
- Admin CRUD for items and categories (商品・カテゴリの管理機能)
- Payment processing (会計処理)
- Printable QR codes for each table (テーブル別QRコード印刷)
- Responsive design for mobile (モバイル対応レスポンシブデザイン)
- Japanese language support (日本語対応)

---

## Tools Used / 使用ツール

- **ChatGPT** - AI coding assistant
- **Claude AI** - AI coding assistant
- **GitHub** - Version control & repository hosting
- **Railway** - Cloud deployment platform
- **VSCode** - Code editor
