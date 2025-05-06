<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>КиноРадар - Ошибка</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

        :root {
            --primary-color: coral;
            --secondary-color: #ff7f50;
            --dark-color: #333;
            --light-color: #fff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Montserrat", Calibri, Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
            color: var(--light-color);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .parallax-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .parallax-bg div {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-1000px) rotate(720deg); }
        }

        .container {
            max-width: 800px;
            padding: 2rem;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            margin: 2rem;
        }

        .title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            color: var(--light-color);
        }

        .error-code {
            font-size: 8rem;
            font-weight: bold;
            line-height: 1;
            margin: 1rem 0;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3);
            color: var(--light-color);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .message {
            font-size: 1.5rem;
            margin: 1rem 0;
            line-height: 1.5;
        }

        .description {
            font-size: 1.2rem;
            margin: 1.5rem 0;
            opacity: 0.9;
        }

        .btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.8rem 2rem;
            background-color: var(--light-color);
            color: var(--primary-color);
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            text-decoration: none;
        }

        .film-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 5rem;
            }
            .title {
                font-size: 2rem;
            }
            .message {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <div class="parallax-bg" id="parallax"></div>
    
    <div class="container">
        <div class="film-icon">🎬</div>
        <div class="title">КиноРадар</div>
        <div class="error-code"><?php echo http_response_code(); ?></div>
        <div class="message">
            <?php
            $error_code = http_response_code();
            $error_messages = [
                400 => 'Неверный запрос',
                401 => 'Не авторизован',
                403 => 'Доступ запрещен',
                404 => 'Страница не найдена',
                500 => 'Внутренняя ошибка сервера',
                503 => 'Сервис недоступен'
            ];
            
            echo $error_messages[$error_code] ?? 'Произошла ошибка';
            ?>
        </div>
        <div class="description">
            <?php
            $error_descriptions = [
                400 => 'Сервер не может обработать ваш запрос из-за неверного синтаксиса.',
                401 => 'Для доступа к этой странице требуется авторизация.',
                403 => 'У вас нет прав для доступа к этой странице.',
                404 => 'Запрошенная страница не существует или была перемещена.',
                500 => 'На сервере произошла внутренняя ошибка. Пожалуйста, попробуйте позже.',
                503 => 'Сервер временно недоступен из-за технических работ.'
            ];
            
            echo $error_descriptions[$error_code] ?? 'Произошла непредвиденная ошибка. Пожалуйста, попробуйте позже.';
            ?>
        </div>
        <a href="../" class="btn">На главную</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const parallax = document.getElementById('parallax');
            
            for (let i = 0; i < 20; i++) {
                const bubble = document.createElement('div');
                const size = Math.random() * 100 + 50;
                bubble.style.width = `${size}px`;
                bubble.style.height = `${size}px`;
                bubble.style.left = `${Math.random() * 100}%`;
                bubble.style.bottom = `-${size}px`;
                bubble.style.animationDuration = `${Math.random() * 20 + 10}s`;
                bubble.style.animationDelay = `${Math.random() * 5}s`;
                parallax.appendChild(bubble);
            }

            document.addEventListener('mousemove', function(e) {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;
                
                parallax.style.transform = `translate(${x * 20}px, ${y * 20}px)`;
            });
        });
    </script>
</body>

</html>