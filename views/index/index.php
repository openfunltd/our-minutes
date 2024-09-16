<?= $this->partial('common/header') ?>
<?php if ($this->user) { ?>
<p>Hello, <?= $this->escape($this->user->email) ?></p>
Your meetings:
<ul>
    <li><a href="/meeting/create">Create a new meeting</a></li>
</ul>
<a href="/user/logout">Logout</a>
<?php } else { ?>
<a href="/user/googlelogin">Google Login</a>
<?php } ?>
<?= $this->partial('common/footer') ?>
