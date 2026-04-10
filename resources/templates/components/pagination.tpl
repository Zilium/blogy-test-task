{if !empty($pagination) && $pagination.total_pages > 1}
    <nav class="pagination">
        <ul class="pagination__list">

            {if $pagination.current_page > 3}
                <li class="pagination__item">
                    <a class="pagination__link" href="{build_url base_url=$pagination.base_url params=$pagination.params page=1}">
                        &laquo;
                    </a>
                </li>
            {/if}

            {if $pagination.has_prev}
                <li class="pagination__item">
                    <a class="pagination__link" href="{build_url base_url=$pagination.base_url params=$pagination.params page=$pagination.prev_page}">
                        &lt;
                    </a>
                </li>
            {/if}

            {if $pagination.current_page - 2 > 0}
                <li class="pagination__item">
                    <a class="pagination__link" href="{build_url base_url=$pagination.base_url params=$pagination.params page=$pagination.current_page-2}">
                        {$pagination.current_page - 2}
                    </a>
                </li>
            {/if}

            {if $pagination.current_page - 1 > 0}
                <li class="pagination__item">
                    <a class="pagination__link" href="{build_url base_url=$pagination.base_url params=$pagination.params page=$pagination.current_page-1}">
                        {$pagination.current_page - 1}
                    </a>
                </li>
            {/if}

            <li class="pagination__item pagination__item--active">
                <span class="pagination__link pagination__link--current">
                    {$pagination.current_page}
                </span>
            </li>

            {if $pagination.current_page + 1 <= $pagination.total_pages}
                <li class="pagination__item">
                    <a class="pagination__link" href="{build_url base_url=$pagination.base_url params=$pagination.params page=$pagination.current_page+1}">
                        {$pagination.current_page + 1}
                    </a>
                </li>
            {/if}

            {if $pagination.current_page + 2 <= $pagination.total_pages}
                <li class="pagination__item">
                    <a class="pagination__link" href="{build_url base_url=$pagination.base_url params=$pagination.params page=$pagination.current_page+2}">
                        {$pagination.current_page + 2}
                    </a>
                </li>
            {/if}

            {if $pagination.has_next}
                <li class="pagination__item">
                    <a class="pagination__link" href="{build_url base_url=$pagination.base_url params=$pagination.params page=$pagination.next_page}">
                        &gt;
                    </a>
                </li>
            {/if}

            {if $pagination.current_page < $pagination.total_pages - 2}
                <li class="pagination__item">
                    <a class="pagination__link" href="{build_url base_url=$pagination.base_url params=$pagination.params page=$pagination.total_pages}">
                        &raquo;
                    </a>
                </li>
            {/if}

        </ul>
    </nav>
{/if}