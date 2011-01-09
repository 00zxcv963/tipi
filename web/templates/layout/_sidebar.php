<h3>章节列表</h3>
<ul>
<?php
foreach($chapt_list as $chapt) {
	$is_current_page = ($current_page_name == $chapt['page_name']);
	echo "<li class='" . ($is_current_page ? "current_page" : "") . "'>";
	echo "<a href='?p={$chapt['page_name']}'>{$chapt['title']}</a>";
	if(isset($chapt['list']) && !empty($chapt['list'])) {
		echo "<ul>";
		foreach($chapt['list'] as $sub_list) {
			$is_current_page = ($current_page_name == $sub_list['page_name']);
			echo "<li class='" . ($is_current_page ? "current_page" : "" ) . "'>";
			echo "<a href='?p={$sub_list['page_name']}'>{$sub_list['title']}</a>";
			echo "</li>";
		}	
		echo "</ul>";
	}
	echo "</li>";
}
?>
</ul>

