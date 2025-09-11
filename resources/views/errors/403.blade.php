<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không có quyền truy cập</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .container {
            text-align: center;
            z-index: 10;
            position: relative;
        }

        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #fff;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }

        .error-message {
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 2rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .back-button {
            display: inline-block;
            padding: 12px 30px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Hiệu ứng con cá tra */
        .fish-container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .fish {
            position: absolute;
            width: 60px;
            height: 40px;
            background: linear-gradient(45deg, #ff6b6b, #ffa500);
            border-radius: 50% 10% 50% 10%;
            animation: swim 8s infinite linear;
            opacity: 0.8;
            filter: drop-shadow(0 0 10px rgba(255, 107, 107, 0.5));
        }

        .fish::before {
            content: '';
            position: absolute;
            width: 15px;
            height: 15px;
            background: #fff;
            border-radius: 50%;
            top: 8px;
            left: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
            animation: blink 3s infinite;
        }

        .fish::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 15px;
            background: linear-gradient(45deg, #ff6b6b, #ffa500);
            border-radius: 0 50% 50% 0;
            top: 12px;
            right: -15px;
            transform: rotate(45deg);
        }

        /* Cá tra đặc biệt */
        .fish.catfish {
            background: linear-gradient(45deg, #8B4513, #D2691E);
            width: 80px;
            height: 50px;
        }

        .fish.catfish::before {
            width: 20px;
            height: 20px;
            top: 10px;
            left: 15px;
        }

        .fish.catfish::after {
            width: 25px;
            height: 20px;
            top: 15px;
            right: -20px;
        }

        .fish:nth-child(1) {
            top: 20%;
            animation-delay: 0s;
            animation-duration: 10s;
        }

        .fish:nth-child(2) {
            top: 40%;
            animation-delay: -2s;
            animation-duration: 12s;
            transform: scale(0.8);
        }

        .fish:nth-child(3) {
            top: 60%;
            animation-delay: -4s;
            animation-duration: 9s;
            transform: scale(1.2);
        }

        .fish:nth-child(4) {
            top: 80%;
            animation-delay: -6s;
            animation-duration: 11s;
            transform: scale(0.9);
        }

        .fish:nth-child(5) {
            top: 30%;
            animation-delay: -3s;
            animation-duration: 13s;
            transform: scale(1.1);
        }

        /* Bong bóng */
        .bubble {
            position: absolute;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: bubble 6s infinite linear;
        }

        .bubble:nth-child(6) {
            left: 10%;
            animation-delay: 0s;
        }

        .bubble:nth-child(7) {
            left: 30%;
            animation-delay: -1s;
        }

        .bubble:nth-child(8) {
            left: 50%;
            animation-delay: -2s;
        }

        .bubble:nth-child(9) {
            left: 70%;
            animation-delay: -3s;
        }

        .bubble:nth-child(10) {
            left: 90%;
            animation-delay: -4s;
        }

        /* Animations */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        @keyframes swim {
            0% {
                left: -100px;
                transform: translateY(0) rotate(0deg);
            }
            25% {
                transform: translateY(-20px) rotate(5deg);
            }
            50% {
                transform: translateY(0) rotate(0deg);
            }
            75% {
                transform: translateY(20px) rotate(-5deg);
            }
            100% {
                left: 100%;
                transform: translateY(0) rotate(0deg);
            }
        }

        @keyframes bubble {
            0% {
                bottom: -20px;
                opacity: 0;
                transform: scale(0);
            }
            10% {
                opacity: 1;
                transform: scale(1);
            }
            90% {
                opacity: 1;
                transform: scale(1);
            }
            100% {
                bottom: 100%;
                opacity: 0;
                transform: scale(0);
            }
        }

        @keyframes blink {
            0%, 90%, 100% {
                opacity: 1;
            }
            95% {
                opacity: 0.3;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .error-code {
                font-size: 5rem;
            }
            
            .error-message {
                font-size: 1.2rem;
                padding: 0 20px;
            }
            
            .fish {
                width: 40px;
                height: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="fish-container">
        <!-- Con cá tra -->
        <div class="fish catfish"></div>
        <div class="fish"></div>
        <div class="fish"></div>
        <div class="fish catfish"></div>
        <div class="fish"></div>
        
        <!-- Bong bóng -->
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>

    <div class="container">
        <div class="error-code">403</div>
        <div class="error-message">
            Bạn không có quyền truy cập chức năng này
        </div>
        <a href="{{ url('/') }}" class="back-button">
            🏠 Về trang chủ
        </a>
    </div>

    <script>
        // Thêm hiệu ứng âm thanh khi click (tùy chọn)
        document.addEventListener('DOMContentLoaded', function() {
            // Tạo thêm một số con cá ngẫu nhiên
            const fishContainer = document.querySelector('.fish-container');
            
            for (let i = 0; i < 3; i++) {
                const fish = document.createElement('div');
                // 30% cơ hội tạo cá tra
                fish.className = Math.random() < 0.3 ? 'fish catfish' : 'fish';
                fish.style.top = Math.random() * 80 + 10 + '%';
                fish.style.animationDelay = Math.random() * 8 + 's';
                fish.style.animationDuration = (Math.random() * 5 + 8) + 's';
                fishContainer.appendChild(fish);
            }
            
            // Thêm bong bóng ngẫu nhiên
            for (let i = 0; i < 5; i++) {
                const bubble = document.createElement('div');
                bubble.className = 'bubble';
                bubble.style.left = Math.random() * 90 + 5 + '%';
                bubble.style.animationDelay = Math.random() * 6 + 's';
                fishContainer.appendChild(bubble);
            }

            // Thêm hiệu ứng click để tạo cá mới
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('back-button')) return;
                
                const fish = document.createElement('div');
                fish.className = Math.random() < 0.5 ? 'fish catfish' : 'fish';
                fish.style.top = e.clientY + 'px';
                fish.style.left = e.clientX + 'px';
                fish.style.animationDuration = '3s';
                fish.style.animationIterationCount = '1';
                fish.style.animationFillMode = 'forwards';
                fishContainer.appendChild(fish);
                
                // Xóa cá sau khi animation kết thúc
                setTimeout(() => {
                    if (fish.parentNode) {
                        fish.parentNode.removeChild(fish);
                    }
                }, 3000);
            });
        });
    </script>
</body>
</html>