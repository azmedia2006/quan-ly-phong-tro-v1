<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đang Phát Triển</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace; /* Font chữ kiểu hacker/code */
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #050505; /* Đen tuyền để LED nổi lên */
            overflow: hidden;
        }

        /* Khung chứa viền LED */
        .rgb-card {
            position: relative;
            width: 450px;
            height: 250px;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 20px;
            overflow: hidden;
        }

        /* Hiệu ứng LED RGB chạy vòng quanh */
        .rgb-card::before {
            content: '';
            position: absolute;
            width: 150px;
            height: 200%;
            background: linear-gradient(#00ccff, #d400d4);
            animation: animateRGB 4s linear infinite;
        }

        .rgb-card::after {
            content: '';
            position: absolute;
            inset: 5px; /* Độ dày của viền LED */
            background: #0f0f0f; /* Màu nền của card bên trong */
            border-radius: 16px;
        }

        @keyframes animateRGB {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Phần nội dung bên trong */
        .content {
            position: relative;
            z-index: 10;
            text-align: center;
            width: 100%;
            padding: 20px;
        }

        h2 {
            color: #fff;
            font-size: 2em;
            letter-spacing: 2px;
            text-shadow: 0 0 10px #00ccff, 0 0 20px #00ccff, 0 0 40px #00ccff; /* Hiệu ứng Neon */
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        p {
            color: #aaa;
            font-size: 1.1em;
            margin-bottom: 20px;
        }

        #countdown {
            color: #d400d4;
            font-weight: bold;
            font-size: 1.5em;
            text-shadow: 0 0 10px #d400d4;
        }

        /* Thanh thời gian (Progress Bar) */
        .progress-bar {
            width: 80%;
            height: 6px;
            background: #222;
            border-radius: 10px;
            margin: 0 auto;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 100%;
            /* Thanh chạy màu RGB */
            background: linear-gradient(90deg, #ff0000, #ffff00, #00ff00, #00ffff, #0000ff, #ff00ff, #ff0000);
            background-size: 200%;
            animation: shrink 5s linear forwards, rgbFlow 2s linear infinite;
        }

        /* Animation tụt thanh thời gian trong 5 giây */
        @keyframes shrink {
            0% { width: 100%; }
            100% { width: 0%; }
        }

        /* Animation cho màu của thanh progress trôi đi */
        @keyframes rgbFlow {
            0% { background-position: 0% 0; }
            100% { background-position: 200% 0; }
        }

    </style>
</head>
<body>

    <div class="rgb-card">
        <div class="content">
            <h2>Đang Nâng Cấp</h2>
            <p>Tự động về trang chủ sau <span id="countdown">5</span>s</p>
            
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        </div>
    </div>

    <script>
        let timeLeft = 5;
        const countdownEl = document.getElementById('countdown');

        // Chạy bộ đếm ngược số
        const timer = setInterval(() => {
            timeLeft--;
            countdownEl.innerText = timeLeft;

            // Hết 5s thì redirect
            if (timeLeft <= 0) {
                clearInterval(timer);
                
                // Thay link trang chủ của bạn vào đây
                window.location.href = "/"; 
            }
        }, 1000); // 1000ms = 1s
    </script>

</body>
</html>