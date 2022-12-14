<?php require ('partials/header.php'); ?>
<h1 class="<?= theme('text-white-75','text-dark') ?>">Contato<?= $title ?></h1>
<p class="<?= theme('text-white-75','text-dark') ?>">Se você quiser entrar em contato <?= strtolower($title) ?>, por favor, sinta-se livre para usar o meu formulário de contato em <a class="<?= theme('text-light', 'text-primary') ?>" href="<?= $website ?>"><?= $website ?></a> ou me enviar um e-mail para <a class="<?= theme('text-light', 'text-primary') ?>" href="mailto:<?= $email ?>"><?= $email ?></a></p>
<?php require ('partials/footer.php'); ?>
