<div class="container">
    <div class="category-page__title title">
        <h1 class="title__h1">{$category.title|escape}</h1>
        <span class="title__count">count: ({$total|default:0})</span>
    </div>
    
    {if !empty($category.description)}
        <div class="category-page__description">
            ({$category.description|escape})
        </div>
    {/if}

    {if !empty($articles)}
    
        <div class="category-page__sort">
            {include file="components/sort.tpl" pagination=$pagination}
        </div>

        {if !empty($articles)}
            <div class="category-page__list">
                {foreach $articles as $article}
                    {include
                        file="components/article/card.tpl"
                        article=$article
                        title_tag='h2'
                    }
                {/foreach}
            </div>

            {include
                file="components/pagination.tpl"
                pagination=$pagination
            }
        {/if}

    {else}
        <div class="category-page__empty">
            No articles found in this category.
        </div>
    {/if}
</div>