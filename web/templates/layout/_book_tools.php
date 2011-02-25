<?php
$page_path = $page->getPagePath();
$page_path_count = count($page_path);
?>

<div id="book_tools" class="clearfix">
	<div id="book_path_navor" class="fl">
		<?php if($page_path_count == 0) : ?>
			<?php echo $page->getTitle(); ?>
		<?php else: ?>
			<?php foreach($page_path as $i => $p): ?>
				<a href='<?php echo url_for_book($p['page_name']); ?>'><?php echo $p['title']; ?></a>
				<?php if($i < $page_path_count - 1) echo '»'; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<?php $headers = $page->getOutlineHeaders(); ?>
	<div id="book_item_tools" class="fr" style="position: relative">
		<a href='#comment' id="comment_link" class="tool_item" title="发表下看法吧!"><span>Comment</span></a>
		<a href='#' id="font_size_changer" class="tool_item" title="调整字体大小"><span>Font Size</span></a>
		<div id="font_size_pannel" class="dialog_box clearfix">
			<a href='javascript:void(0);' id='font_size_decr' title="减小字体">-</a>
			<a href='javascript:void(0);' id='font_size_default' title="恢复默认">1</a>
			<a href='javascript:void(0);' id='font_size_incr' title="增大字体">+</a>
		</div>
		<a href='#' id="share_page" class="tool_item" title="喜欢这篇文章? 分享吧!"><span>Share</span></a>
		<?php if(count($headers)): ?>
			<a id="page_outline_button" class='first_border_tool tool_item' href="#" title="查看小节大纲"><span>Toc</span></a>
			<div id="page_outline" class='dialog_box'>
				<ul>
				<?php foreach($headers as $header): ?>
					<li><a href='#<?php echo $header['text']; ?>'><?php echo $header['text']; ?></a></li>
				<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<!--
		<a href='#back_top' id='back_to_top' class='tool_item <?php if(!count($headers)) { echo "first_border_tool'"; } ?>' title="返回页首"></a>
		-->
	</div>
</div>
