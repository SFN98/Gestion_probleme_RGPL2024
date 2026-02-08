<?php
/**
 * Layout commun — structure HTML, design system, zone contenu
 * Variables attendues : $pageTitle (string), $content (string ou nom de vue à inclure)
 */
$pageTitle = $pageTitle ?? 'Plateforme RGPL 2024';
$baseUrl = defined('PUBLIC_URL') ? PUBLIC_URL : '/public';
$appBase = defined('APP_BASE_URL') ? APP_BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/design-system.css">
  <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/base.css">
  <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/components.css">
</head>
<body>
  <div id="layout-loader-wrapper" style="display:none" aria-hidden="true">
    <?php require TEMPLATES_PATH . '/partials/loader.php'; ?>
  </div>
  <main class="container">
    <?php
    if (isset($content) && is_string($content) && $content !== '') {
        echo $content;
    } elseif (isset($contentView) && is_string($contentView)) {
        $path = TEMPLATES_PATH . '/pages/' . $contentView . '.php';
        if (file_exists($path)) {
            include $path;
        }
    }
    ?>
  </main>
  <script>
    window.appLoaderWrapper = document.getElementById('layout-loader-wrapper');
    window.appBase = <?php echo json_encode($appBase); ?>;
  </script>
<?php if (!empty($scripts) && is_array($scripts)): ?>
  <?php foreach ($scripts as $s): ?>
  <script src="<?php echo $baseUrl; ?>/js/<?php echo htmlspecialchars($s); ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
