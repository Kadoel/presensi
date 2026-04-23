<?= $this->extend('theme/body'); ?>

<?= $this->section('content'); ?>

<div class="row">
    <div class="col-md-12">
        <ul>
            <?php
            foreach (session()->get() as $session => $value):
            ?>
                <li><?= $session; ?> => <?= $value; ?></li>
            <?php
            endforeach;
            ?>
        </ul>
    </div>
</div>

<?= $this->endSection('content'); ?>