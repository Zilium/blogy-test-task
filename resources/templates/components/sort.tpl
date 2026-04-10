<div class="sort">
    <a class="sort__link {if ($smarty.get.sort|default:'date') eq 'date'}sort__link--active{/if}"
        href="{build_url base_url=$pagination.base_url params=$smarty.get sort='date' page=1}">
        By date
    </a>

    <a class="sort__link {if ($smarty.get.sort|default:'date') eq 'views'}sort__link--active{/if}"
        href="{build_url base_url=$pagination.base_url params=$smarty.get sort='views' page=1}">
        By views
    </a>
</div>