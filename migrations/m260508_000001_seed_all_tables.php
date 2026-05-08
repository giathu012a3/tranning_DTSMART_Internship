<?php

use yii\db\Migration;

/**
 * Seed data for all tables – khớp đúng cột theo migration files
 */
class m260508_000001_seed_all_tables extends Migration
{
    public function safeUp()
    {
        $now = time();

        // ===== 1. MEMBERSHIP LEVELS =====
        $this->batchInsert('membership_levels',
            ['name', 'points_required', 'discount_rate', 'status', 'created_at', 'updated_at'],
            [
                ['Đồng',    0,    0.00, 1, $now, $now],
                ['Bạc',   500,   3.00, 1, $now, $now],
                ['Vàng',  2000,  5.00, 1, $now, $now],
                ['Bạch Kim', 5000, 10.00, 1, $now, $now],
            ]
        );

        // ===== 2. ROLES =====
        // Columns: id, name, description, status, created_at, updated_at
        $this->batchInsert('roles',
            ['name', 'description', 'status', 'created_at', 'updated_at'],
            [
                ['Quản trị viên', 'Toàn quyền hệ thống',    1, $now, $now],
                ['Biên tập viên', 'Quản lý bài viết',       1, $now, $now],
                ['Khách hàng',    'Người dùng thông thường', 1, $now, $now],
            ]
        );

        // ===== 3. PERMISSIONS =====
        // Columns: id, name, slug, module, status, created_at, updated_at
        $this->batchInsert('permissions',
            ['name', 'slug', 'module', 'status', 'created_at', 'updated_at'],
            [
                ['Xem sản phẩm',       'product.view',   'product', 1, $now, $now],
                ['Tạo sản phẩm',       'product.create', 'product', 1, $now, $now],
                ['Sửa sản phẩm',       'product.update', 'product', 1, $now, $now],
                ['Xóa sản phẩm',       'product.delete', 'product', 1, $now, $now],
                ['Xem đơn hàng',       'order.view',     'order',   1, $now, $now],
                ['Sửa đơn hàng',       'order.update',   'order',   1, $now, $now],
                ['Xem bài viết',       'article.view',   'article', 1, $now, $now],
                ['Tạo bài viết',       'article.create', 'article', 1, $now, $now],
                ['Sửa bài viết',       'article.update', 'article', 1, $now, $now],
                ['Xóa bài viết',       'article.delete', 'article', 1, $now, $now],
                ['Quản lý người dùng', 'user.manage',    'user',    1, $now, $now],
            ]
        );

        // ===== 4. ROLE PERMISSIONS =====
        // Columns: id, role_id, permission_id, created_at, updated_at
        $rp = [];
        // Admin (role_id=1) => tất cả quyền 1-11
        for ($i = 1; $i <= 11; $i++) {
            $rp[] = [1, $i, $now, $now];
        }
        // Editor (role_id=2) => quyền article 7-10
        foreach ([7, 8, 9, 10] as $pid) {
            $rp[] = [2, $pid, $now, $now];
        }
        // Customer (role_id=3) => chỉ xem 1, 5, 7
        foreach ([1, 5, 7] as $pid) {
            $rp[] = [3, $pid, $now, $now];
        }
        $this->batchInsert('role_permissions', ['role_id', 'permission_id', 'created_at', 'updated_at'], $rp);

        // ===== 5. USERS =====
        // Columns: id, username, email, password_hash, member_ship_id, total_points, status, created_at, updated_at
        $hash = '$2y$13$hashedpasswordplaceholder1234567890abcdef';
        $this->batchInsert('users',
            ['username', 'email', 'password_hash', 'member_ship_id', 'total_points', 'status', 'created_at', 'updated_at'],
            [
                ['admin',        'admin@example.com',       $hash, 4, 9999, 1, $now, $now],
                ['editor01',     'editor@example.com',      $hash, 1,  100, 1, $now, $now],
                ['nguyen_van_a', 'nguyenvana@gmail.com',    $hash, 2,  750, 1, $now, $now],
                ['tran_thi_b',   'tranthib@gmail.com',      $hash, 1,  200, 1, $now, $now],
                ['le_van_c',     'levanc@gmail.com',        $hash, 3, 3500, 1, $now, $now],
            ]
        );

        // ===== 6. USER ADDRESSES =====
        // Columns: id, user_id, full_name, phone, address, is_default, status, created_at, updated_at
        $this->batchInsert('user_addresses',
            ['user_id', 'full_name', 'phone', 'address', 'is_default', 'status', 'created_at', 'updated_at'],
            [
                [3, 'Nguyễn Văn A', '0901234567', '12 Nguyễn Huệ, Quận 1, TP.HCM',       1, 1, $now, $now],
                [4, 'Trần Thị B',   '0912345678', '45 Lê Lợi, Hoàn Kiếm, Hà Nội',        1, 1, $now, $now],
                [5, 'Lê Văn C',     '0923456789', '78 Trần Phú, Hải Châu, Đà Nẵng',      1, 1, $now, $now],
            ]
        );

        // ===== 7. CATEGORIES =====
        $this->batchInsert('categories',
            ['name', 'status', 'created_at', 'updated_at'],
            [
                ['Điện tử',          1, $now, $now],
                ['Thời trang',       1, $now, $now],
                ['Gia dụng',         1, $now, $now],
                ['Sách & Văn phòng', 1, $now, $now],
                ['Thể thao',         1, $now, $now],
            ]
        );

        // ===== 8. PRODUCTS =====
        // Columns: id, name, price, stock, status, description, category_id, created_at, updated_at, deleted_at
        $this->batchInsert('products',
            ['name', 'price', 'stock', 'status', 'description', 'category_id', 'created_at', 'updated_at', 'deleted_at'],
            [
                ['iPhone 15 Pro',              29990000, 50,  1, 'iPhone 15 Pro 256GB – chip A17 Pro, camera 48MP',      1, $now, $now, null],
                ['Samsung Galaxy S24',          22990000, 80,  1, 'Samsung Galaxy S24 256GB – AI camera, màn AMOLED',     1, $now, $now, null],
                ['Tai nghe Sony WH-1000XM5',    6990000, 120, 1, 'Chống ồn chủ động ANC, pin 30 giờ',                    1, $now, $now, null],
                ['Áo Polo Nam Cotton',           349000, 500, 1, 'Chất liệu cotton cao cấp, thoáng mát, nhiều màu',      2, $now, $now, null],
                ['Quần Jean Nữ Slim Fit',        459000, 300, 1, 'Co giãn 4 chiều, form chuẩn, bền màu',                 2, $now, $now, null],
                ['Nồi cơm điện Sunhouse 1.8L',   890000, 150, 1, 'Dung tích 1.8L, công nghệ nấu 3D, giữ ấm 24h',        3, $now, $now, null],
                ['Máy xay sinh tố Philips',      650000, 200, 1, 'Công suất 600W, 3 tốc độ, dễ vệ sinh',                 3, $now, $now, null],
                ['Sách Lập Trình PHP Cơ Bản',    150000, 400, 1, 'Dành cho người mới bắt đầu học PHP và web',           4, $now, $now, null],
                ['Balo Laptop 15 inch',           399000, 250, 1, 'Chống nước, ngăn đựng laptop riêng, nhẹ 0.8kg',       4, $now, $now, null],
                ['Giày Running Nike Air',        1290000, 180, 1, 'Đế foam Cushlon, phù hợp chạy bộ đường dài',          5, $now, $now, null],
            ]
        );

        // ===== 9. COUPONS =====
        $start = strtotime('2026-01-01');
        $end   = strtotime('2026-12-31');
        $this->batchInsert('coupons',
            ['code', 'type', 'value', 'max_amount', 'min_purchase', 'usage_limit', 'status', 'start_date', 'expiry_date', 'created_at', 'updated_at'],
            [
                ['WELCOME10', 'percent',  10.00,  50000,    null,  100, 1, $start, $end, $now, $now],
                ['SUMMER20',  'percent',  20.00, 100000, 500000,    50, 1, $start, $end, $now, $now],
                ['SALE50K',   'fixed',   50000,    null, 200000,   200, 1, $start, $end, $now, $now],
                ['VIP15',     'percent',  15.00, 150000, 1000000,   30, 1, $start, $end, $now, $now],
                ['FREESHIP',  'fixed',   30000,    null,    null,  500, 1, $start, $end, $now, $now],
            ]
        );

        // ===== 10. CARTS =====
        // Columns: id, user_id, created_at, updated_at
        $this->batchInsert('carts',
            ['user_id', 'created_at', 'updated_at'],
            [
                [3, $now, $now],
                [4, $now, $now],
                [5, $now, $now],
            ]
        );

        // ===== 11. CART DETAILS =====
        // Columns: id, cart_id, product_id, quantity
        $this->batchInsert('cart_details',
            ['cart_id', 'product_id', 'quantity'],
            [
                [1, 1,  1],
                [1, 4,  2],
                [2, 6,  1],
                [2, 10, 1],
                [3, 2,  1],
            ]
        );

        // ===== 12. ORDERS =====
        $this->batchInsert('orders',
            ['user_id', 'full_name', 'email', 'phone', 'address', 'membership_level_id', 'discount_amount', 'total', 'final_total', 'payment_method', 'status', 'created_at', 'updated_at'],
            [
                [3, 'Nguyễn Văn A', 'nguyenvana@gmail.com', '0901234567', '12 Nguyễn Huệ, Q1, HCM',  2,        0, 29990000, 29990000, 'cod',           2, $now, $now],
                [4, 'Trần Thị B',   'tranthib@gmail.com',   '0912345678', '45 Lê Lợi, Hoàn Kiếm, HN', 1,  2299000, 22990000, 20691000, 'bank_transfer', 2, $now, $now],
                [5, 'Lê Văn C',     'levanc@gmail.com',     '0923456789', '78 Trần Phú, Hải Châu, ĐN', 3,   650000,  6990000,  6340000, 'cod',           1, $now, $now],
                [3, 'Nguyễn Văn A', 'nguyenvana@gmail.com', '0901234567', '12 Nguyễn Huệ, Q1, HCM',  2,    34900,   698000,   663100, 'cod',           3, $now, $now],
            ]
        );

        // ===== 13. ORDER DETAILS =====
        $this->batchInsert('order_details',
            ['order_id', 'product_id', 'quantity', 'price', 'created_at', 'updated_at'],
            [
                [1, 1, 1, 29990000, $now, $now],
                [2, 2, 1, 22990000, $now, $now],
                [3, 3, 1,  6990000, $now, $now],
                [4, 4, 2,   349000, $now, $now],
            ]
        );

        // ===== 14. COUPON USAGES =====
        // Columns: id, coupon_id, user_id, order_id, applied_code, applied_type, applied_value, applied_max_amount, created_at
        $this->batchInsert('coupon_usages',
            ['coupon_id', 'user_id', 'order_id', 'applied_code', 'applied_type', 'applied_value', 'applied_max_amount', 'created_at'],
            [
                [2, 4, 2, 'SUMMER20',  'percent', 20.00, 100000, $now],
                [3, 5, 3, 'SALE50K',   'fixed',   50000, null,   $now],
                [1, 3, 4, 'WELCOME10', 'percent', 10.00,  50000, $now],
            ]
        );

        // ===== 15. TAGS =====
        $this->batchInsert('tags',
            ['name', 'slug', 'status', 'created_at', 'updated_at'],
            [
                ['Công nghệ',  'cong-nghe',  1, $now, $now],
                ['Thời trang', 'thoi-trang', 1, $now, $now],
                ['Mẹo vặt',    'meo-vat',   1, $now, $now],
                ['Review',     'review',    1, $now, $now],
                ['Khuyến mãi', 'khuyen-mai', 1, $now, $now],
                ['Lifestyle',  'lifestyle', 1, $now, $now],
            ]
        );

        // ===== 16. ARTICLES =====
        // Columns: id, title, content, slug, excerpt, like_count, author_id, status, created_at, updated_at
        $this->batchInsert('articles',
            ['title', 'content', 'slug', 'excerpt', 'like_count', 'author_id', 'status', 'created_at', 'updated_at'],
            [
                [
                    'Top 5 smartphone đáng mua nhất 2026',
                    '<p>Dưới đây là danh sách 5 chiếc smartphone nổi bật nhất trong năm 2026 với hiệu năng vượt trội, camera đỉnh cao và pin bền bỉ. Cùng khám phá nhé!</p>',
                    'top-5-smartphone-dang-mua-nhat-2026',
                    'Tổng hợp 5 smartphone đáng mua nhất 2026 theo từng phân khúc giá.',
                    45, 2, 1, $now, $now,
                ],
                [
                    'Xu hướng thời trang hè 2026',
                    '<p>Mùa hè 2026 mang đến những xu hướng thời trang tươi sáng, năng động với màu sắc rực rỡ và chất liệu thoáng mát, phù hợp khí hậu nhiệt đới.</p>',
                    'xu-huong-thoi-trang-he-2026',
                    'Khám phá các xu hướng thời trang nổi bật mùa hè năm nay.',
                    30, 2, 1, $now, $now,
                ],
                [
                    'Hướng dẫn chọn nồi cơm điện phù hợp',
                    '<p>Chọn nồi cơm điện cần quan tâm đến dung tích, công nghệ nấu và thương hiệu. Bài viết này giúp bạn lựa chọn đúng chiếc nồi phù hợp với gia đình.</p>',
                    'huong-dan-chon-noi-com-dien-phu-hop',
                    'Bí kíp chọn nồi cơm điện phù hợp cho từng gia đình.',
                    18, 2, 1, $now, $now,
                ],
            ]
        );

        // ===== 17. ARTICLE TAGS =====
        // Columns: id, article_id, tag_id, created_at, updated_at
        $this->batchInsert('article_tags',
            ['article_id', 'tag_id', 'created_at', 'updated_at'],
            [
                [1, 1, $now, $now], // bài 1 – Công nghệ
                [1, 4, $now, $now], // bài 1 – Review
                [2, 2, $now, $now], // bài 2 – Thời trang
                [2, 6, $now, $now], // bài 2 – Lifestyle
                [3, 3, $now, $now], // bài 3 – Mẹo vặt
            ]
        );

        // ===== 18. PRODUCT ARTICLES =====
        // Columns: id, product_id, article_id, created_at, updated_at
        $this->batchInsert('product_articles',
            ['product_id', 'article_id', 'created_at', 'updated_at'],
            [
                [1, 1, $now, $now], // iPhone 15 Pro <-> bài top smartphone
                [2, 1, $now, $now], // Samsung S24   <-> bài top smartphone
                [6, 3, $now, $now], // Nồi cơm điện  <-> bài chọn nồi
            ]
        );

        // ===== 19. ARTICLE COMMENTS =====
        // Columns: id, article_id, user_id, content, parent_id, status, created_at, updated_at
        $this->batchInsert('article_comments',
            ['article_id', 'user_id', 'content', 'parent_id', 'status', 'created_at', 'updated_at'],
            [
                [1, 3, 'Bài viết rất hữu ích, cảm ơn tác giả!',              null, 1, $now, $now],
                [1, 4, 'Mình đang dùng iPhone 15 Pro, thật sự rất ngon!',    null, 1, $now, $now],
                [1, 5, 'Samsung cũng không tệ, camera đẹp hơn mong đợi.',   null, 1, $now, $now],
                [2, 3, 'Xu hướng năm nay thật thú vị, mình thích lắm!',     null, 1, $now, $now],
                [3, 4, 'Mình mới mua nồi Sunhouse, dùng tốt lắm mọi người.', null, 1, $now, $now],
            ]
        );

        // ===== 20. ARTICLE LIKES =====
        // Columns: id, article_id, user_id, created_at, updated_at
        $this->batchInsert('article_likes',
            ['article_id', 'user_id', 'created_at', 'updated_at'],
            [
                [1, 3, $now, $now],
                [1, 4, $now, $now],
                [1, 5, $now, $now],
                [2, 3, $now, $now],
                [2, 5, $now, $now],
                [3, 4, $now, $now],
            ]
        );
    }

    public function safeDown()
    {
        $this->delete('article_likes');
        $this->delete('article_comments');
        $this->delete('product_articles');
        $this->delete('article_tags');
        $this->delete('articles');
        $this->delete('tags');
        $this->delete('coupon_usages');
        $this->delete('order_details');
        $this->delete('orders');
        $this->delete('cart_details');
        $this->delete('carts');
        $this->delete('coupons');
        $this->delete('products');
        $this->delete('categories');
        $this->delete('user_addresses');
        $this->delete('users');
        $this->delete('role_permissions');
        $this->delete('permissions');
        $this->delete('roles');
        $this->delete('membership_levels');
    }
}
