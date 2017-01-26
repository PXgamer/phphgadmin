<!DOCTYPE html>
<html>
<head>
    <?= $this->load->view('include/header', [], true); ?>
</head>
<body>
<?= $this->load->view('include/navbar', [], true); ?>
<div class="container">
    <?= $view ?? '' ?>
</div>
</body>
</html>