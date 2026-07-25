<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?><?= APP_NAME ?></title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- OdontoGest Variables & Base -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/variables.css">
    <!-- Dark Mode Overrides (después de variables para mayor especificidad) -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/dark-mode.css">

    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?= APP_URL . htmlspecialchars($css) ?>">
    <?php endforeach; endif; ?>

    <!-- Anti-flash: aplica el tema guardado antes de renderizar el body -->
    <script>
        (function(){
            const t = localStorage.getItem('og_theme');
            if (t === 'dark') document.documentElement.setAttribute('data-theme','dark');
        })();
    </script>
</head>
<body>
