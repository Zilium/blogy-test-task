<div class="container">
    <article class="article-page">
        <header class="article-page__header">
            <h1 class="article-page__title">
                {$article.title|escape}
            </h1>

            <div class="article-page__meta">
                {if !empty($article.published_at)}
                    <span class="article-page__date">
                        {$article.published_at}
                    </span>
                {/if}

                <span class="article-page__views">
                    Views: {$article.views|default:0}
                </span>
            </div>
        </header>

        {if !empty($article.image)}
            <div class="article-page__image">
                <img
                    src="{$app.site}{$article.image}"
                    alt="{$article.title|escape}"
                    width="860"
                    height="570"
                    loading="eager"
                >
            </div>
        {/if}

        {if !empty($article.description)}
            <div class="article-page__description">
                {$article.description|escape}
            </div>
        {/if}

        {if !empty($article.content)}
            <div class="article-page__content">
                {$article.content nofilter}
            </div>
        {/if}
    </article>

    {if !empty($related.articles)}
        <section class="article-related">
            <h2 class="article-related__title">Related articles</h2>
            <div class="article-related__list">
                {foreach $related.articles as $article}
                    {include
                        file="components/article/card.tpl"
                        article=$article
                        title_tag='h3'
                    }
                {/foreach}
            </div>
        </section>
    {/if}
</div>