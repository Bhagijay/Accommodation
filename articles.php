<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommo-NSBM</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css\all.min.css">
</head>
<body>
<nav>
<div class="nav__logo">Accommo-NSBM</div>
<ul class="nav__links">
<li class="link"><a href="index.php">Home</a></li>
<li class="link"><a href="#footer_section">Contact</a></li>
<li class="link"><a href="login.php">Login</a></li>
</ul>
</nav>

    <section class="section__container card__container" id="articles_section">
        <h2 class="section__header">All Articles</h2>
        <div class="articles_card__content">
            <div class="swiper-wrapper">
                <?php
                include("config.php");
                $query = "SELECT title, content FROM articles";
                $result = mysqli_query($connection, $query);
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<article class="card__article">';
                        echo '<div class="card__data">';
                        echo '<h3 class="card__name">' . $row['title'] . '</h3>';
                        echo '<p class="card__description">' . substr($row['content'], 0, 400) . '</p>';
                        echo '<a href="#" class="card__button read-article" data-content="' . htmlentities($row['content']) . '">Read</a>';
                        echo '</div>';
                        echo '</article>';
                    }
                    mysqli_free_result($result);
                } else {
                    echo "Error: " . $query . "<br>" . mysqli_error($connection);
                }
                mysqli_close($connection);
                ?>
            </div>
        </div>
    </section>

    <div id="popup" class="popup-container">
        <div class="popup-content">
            <button id="close-popup" class="close-popup-btn"><i class="fas fa-times"></i></button>
            <div id="article-content"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const readButtons = document.querySelectorAll('.read-article');
            const popup = document.getElementById('popup');
            const closeButton = document.getElementById('close-popup');
            const articleContent = document.getElementById('article-content');

            readButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const articleDescription = this.dataset.content;
                    const articleTitle = this.parentElement.querySelector('.card__name').textContent;
                    articleContent.innerHTML = `<h3>${articleTitle}</h3><p>${articleDescription}</p>`;
                    popup.style.display = 'block';
                });
            });

            closeButton.addEventListener('click', function() {
                popup.style.display = 'none';
            });
        });
    </script>

<?php
    include "footer.php";
    ?>
</body>
</html>
