{foreach $pages as $page}
    <li>
        <a href="javascript:" class="filter-page" data-name="{$page.page}">
            {$page.page}
        </a>
    </li>
{/foreach}