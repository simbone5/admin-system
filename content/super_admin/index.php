<?php
	checkSuperAdmin();
	

	$adminPath = ADMIN_PATH;
	$html = <<<HTML
		<div class='halfWidth'>
			<p>Super admin settings</p>
			
			<ul class="bulleted">
				<li><a href='/{$adminPath}/super_admin/add_templates' title='Add template'>Add Template</a></li>
				<li><a href='/{$adminPath}/super_admin/update_fields' title='Update fields'>Update Fields</a></li>
				<li><a href='/{$adminPath}/super_admin/manage_gallery_types' title='Manage Gallery Type'>Manage Gallery Type</a></li>
				<li><a href='/{$adminPath}/super_admin/manage_boards' title='Manage Boards'>Manage Boards</a></li>
			</ul>
		</div>
HTML;
	
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setBreadcrumb(array("Super Admin"));
	$p->printPage();
	
?>