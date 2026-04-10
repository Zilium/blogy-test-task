{assign var = heading_tag value = $title_tag|default:'h3'}

<article class="article-card">
    <div class="article-card__media">
        <img 
            class="article-card__image" 
            src="{$app.site}{$article.image}"
            alt="{$article.title|escape}"
            width="470"
            height="300"
            loading="lazy"
        >
    </div>

    <div class="article-card__content">
        <{$heading_tag} class="article-card__title">
            <a class="article-card__title-link" href="{$app.site}/article/{$article.id|escape:'url'}">
                {$article.title|escape}
            </a>
        </{$heading_tag}>

        <div class="article-card__meta">
            <span class="article-card__date">{$article.published_at}</span>

            <span class="article-card__views">
                Views: {$article.views|default:0}
            </span>
        </div>

        {if !empty($article.description)}
            <div class="article-card__description">
                {$article.description|escape}
            </div>
        {/if}

        <div class="article-card__action">
            <a class="article-card__action-link" href="{$app.site}/article/{$article.id|escape:'url'}">
                {$link_text|default:'Continue Reading'}
            </a>
        </div>
    </div>
</article>