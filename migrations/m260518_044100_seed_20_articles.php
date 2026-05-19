<?php

use yii\db\Migration;

/**
 * Class m260518_044100_seed_20_articles
 */
class m260518_044100_seed_20_articles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Lấy ID của user đầu tiên trong hệ thống làm tác giả
        $authorId = (new \yii\db\Query())->select('id')->from('users')->limit(1)->scalar();
        if (!$authorId) {
            $authorId = 1; // Fallback
        }

        $articles = [];
        $time = time();
        
        $topics = [
            'Công nghệ AI', 'Thời trang mùa hè', 'Kinh nghiệm lập trình', 
            'Bí kíp săn Sale', 'Đánh giá sản phẩm', 'Xu hướng E-commerce',
            'Cách tăng doanh thu', 'Mẹo dùng Yii2'
        ];
        
        for ($i = 1; $i <= 20; $i++) {
            $topic = $topics[array_rand($topics)];
            // Tạo tiêu đề hấp dẫn
            $title = "Khám phá $topic phần $i: Những điều bạn chưa từng biết năm 2026";
            
            // Tạo slug đơn giản không dấu
            $slugTopic = strtolower(str_replace(' ', '-', $topic));
            // Bỏ dấu tiếng việt cho slug (cơ bản)
            $slugTopic = preg_replace('/(á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ)/', 'a', $slugTopic);
            $slugTopic = preg_replace('/(é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ)/', 'e', $slugTopic);
            $slugTopic = preg_replace('/(í|ì|ỉ|ĩ|ị)/', 'i', $slugTopic);
            $slugTopic = preg_replace('/(ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ)/', 'o', $slugTopic);
            $slugTopic = preg_replace('/(ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự)/', 'u', $slugTopic);
            $slugTopic = preg_replace('/(ý|ỳ|ỷ|ỹ|ỵ)/', 'y', $slugTopic);
            $slugTopic = preg_replace('/(đ)/', 'd', $slugTopic);
            
            $slug = "kham-pha-" . $slugTopic . "-phan-$i-" . rand(10000, 99999);
            
            $articles[] = [
                $title,
                "Đây là nội dung chi tiết của bài viết số $i. Bài viết này đi sâu vào phân tích các khía cạnh quan trọng của $topic trong bối cảnh thị trường hiện đại.\n\nChúng tôi sẽ bóc tách chi tiết về những ưu và nhược điểm, đồng thời đưa ra những lời khuyên hữu ích có tính ứng dụng cao cho bạn đọc. Hãy cùng ghi chú lại những thông tin thú vị nhất để áp dụng vào công việc của bạn nhé!", // content
                $slug,
                "Tóm tắt siêu ngắn gọn về $topic dành cho người bận rộn. Click để xem ngay chi tiết bài viết số $i.", // excerpt
                rand(10, 999), // like_count
                $authorId, // author_id
                1, // status = 1 (Active)
                $time - rand(1000, 86400 * 30), // created_at (random thời gian trong 30 ngày qua)
                $time, // updated_at
            ];
        }

        // Chèn 20 dòng vào Database chỉ với 1 câu Query (Tối ưu hiệu năng)
        $this->batchInsert('articles', 
            ['title', 'content', 'slug', 'excerpt', 'like_count', 'author_id', 'status', 'created_at', 'updated_at'], 
            $articles
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Xóa những bài viết có slug chứa từ khóa lúc tạo
        $this->delete('articles', ['like', 'slug', 'kham-pha-']);
    }
}
