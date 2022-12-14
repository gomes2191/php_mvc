<nav class="navbar navbar-expand-sm ">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nav-content" aria-controls="nav-content" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="nav-content">
    <ul class="navbar-nav <?=theme('bg-dark', 'bg-white')?>">
        <li class="nav-item">
            <a class="nav-link <?=theme('text-light', 'text-primary')?>" href="/">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?=theme('text-light', 'text-primary')?>" href="/users">Usuários</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?=theme('text-light', 'text-primary')?>" href="/about">Sobre</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?=theme('text-light', 'text-primary')?>" href="/contact">Contato</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?=theme('text-light', 'text-primary')?>" href="#" onclick="toggleDarkMode()">Dark Mode</a>
        </li>
    </ul>
    </div>
</nav>