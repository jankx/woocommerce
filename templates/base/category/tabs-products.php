<div class="jankx-ecommerce category-tabs-products">
    <ul class="jankx-tabs">
        <?php foreach ($tabs as $tab => $data) : ?>
            <li data-tab="<?php echo array_get((array) $data, 'tab', $data); ?>">
                <?php if (isset($data['url'])) : ?>
                    <a href="<?php echo $data['url']; ?>">
                        <?php echo $tab; ?>
                    </a>
                <?php else : ?>
                    <?php echo $tab; ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="jankx-tab-content">
        <?php echo $first_tab_content; ?>
    </div>
    <?php if (!empty($readmore)) : ?>
    <div class="jankx-read view-all-link">
        <a href="<?php echo array_get($readmore, 'url', '#');  ?>">
            <?php echo array_get($readmore, 'text', __('Read more', 'jankx')); ?>
        </a>
    </div>
    <?php endif; ?>
</div>