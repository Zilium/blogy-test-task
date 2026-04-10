<div class="container">
    {if !empty($categories)}
        <div class="home-categories">
            {foreach $categories as $category}
                <section class="category-section">
                    <div class="category-section__header">
                        <div class="category-section__title">
                            <a href="{$app.site}/category/{$category.id|escape:'url'}">
                                {$category.title|escape}
                            </a>
                        </div>

                        <div class="category-section__action">
                            <a class="category-section__link" href="{$app.site}/category/{$category.id|escape:'url'}">
                                View All
                            </a>
                        </div>
                    </div>

                    {if !empty($category.articles)}
                        <div class="category-section__articles">
                            {foreach $category.articles as $article}
                                {include
                                    file="components/article/card.tpl"
                                    article=$article
                                    title_tag='div'
                                }
                            {/foreach}
                        </div>
                    {/if}
                </section>
            {/foreach}
        </div>
    {/if}
</div>