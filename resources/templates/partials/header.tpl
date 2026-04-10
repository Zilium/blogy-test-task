<header class="header">
    <div class="container">
        <div class="header__top">
            <div class="header__row">
                <div class="header__logo">

                    {if $route.controller !== 'home'}
                        <a href="{$app.site|escape}">
                    {/if}
                        <span class="logo">Blogy</span>
                    {if $route.controller !== 'home'}
                        </a>
                    {/if}

                </div>
            </div>
        </div>
    </div>
</header>