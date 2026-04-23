<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle ?? 'ICICIDS') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
<?php if ($flash !== null): ?>
    <div class="alert <?= h((string)$flash['type']) ?>"><?= h((string)$flash['message']) ?></div>
<?php endif; ?>
