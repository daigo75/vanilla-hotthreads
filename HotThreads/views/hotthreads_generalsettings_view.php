<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/
?>
<div class="HotThreadsPlugin">
	<div class="Header">
		<h1><?php echo T($this->Data['Title']); ?></h1>
		<div class="Info">
			<?php echo T($this->Data['PluginDescription']); ?>
		</div>
	</div>
	<div class="Content">
		<?php
			echo $this->Form->Open();
			echo $this->Form->Errors();
		?>
		<fieldset>
			<legend>
				<h3><?php echo T('General Settings'); ?></h3>
			</legend>
			<ul>
				<li><?php
					echo $this->Form->Label(T('Maximum amount of entries to display'),
																		'Plugin.HotThreadsPlugin.Count');
					echo Wrap(T('The Hot Threads widget will display this amount of entries.'),
										'div',
										array('class' => 'Info',));
					echo $this->Form->Textbox('Plugin.HotThreadsPlugin.MaxEntriesToDisplay');
				?></li>
				<li><?php
					echo $this->Form->Label(T('On which pages would you like to display the widget?'),
																		'Plugin.HotThreadsPlugin.DisplayPageSet');
					echo $this->Form->DropDown('Plugin.HotThreadsPlugin.DisplayPageSet',array(
								HOTTHREADS_PAGESET_ALL => T('Discussions and Announcements'),
								HOTTHREADS_PAGESET_DISCUSSIONS => T('Only Discussions'),
								HOTTHREADS_PAGESET_ANNOUNCEMENTS => T('Only Announcements'),
					));
				?></li>
				<li><?php
					echo $this->Form->Label(T('Refresh Hot Threads widget every X seconds'),
																	'Plugin.HotThreadsPlugin.AutoUpdateDelay');
					echo Wrap(T('Set this value to zero to disable automatic refresh.'),
										'div',
										array('class' => 'Info',));
					echo $this->Form->Textbox('Plugin.HotThreadsPlugin.AutoUpdateDelay');
				?></li>
			</ul>
		</fieldset>
		<fieldset>
			<legend>
				<h3><?php echo T('Thresholds'); ?></h3>
				<?php
					echo Wrap(T('Thresholds are used to determine if a thread (discussion) ' .
											'should be considered "hot". ' .
											'Please note that a thread only needs to be over one ' .
											'threshold to be considered "hot". That is, a thread must ' .
											'have <strong>either</strong> a certain amount of Views, ' .
											'<strong>or</strong> a certain amount of Comments, ' .
											'<strong>or</strong> both.'),
										'div',
										array('class' => 'Info',));
				?>
			</legend>
			<div class="Thresholds Help Aside"><?php
				echo Wrap(T('How do Thresholds work?'),
									'h4',
									array('class' => 'Title',));
				echo Wrap(T('Thresholds are used to determine if a thread (discussion) ' .
										'should be considered "hot", and also how hot they are.'),
									'div',
									array('class' => 'Info',));
				echo Wrap(T('The following example assumes a <strong>View Threshold</strong> of 100 and ' .
										'a <strong>Comments Threshold</strong> of 10.'),
									'div',
									array('class' => 'Info',));
				echo "<ul>\n";
				echo Wrap(T('Discussion X and Y have both 10 Views and 1 Comment. They are <strong>not</strong> hot.'),
									'li');
				echo Wrap(T('Discussion X reaches 100 Views, but still 1 Comment. Discussion X ' .
										'<strong>is hot</strong>, since it passed the Views Threshold.'),
									'li');
				echo Wrap(T('Discussion Y reaches 20 Views and 10 Comments. Discussion Y ' .
										'<strong>is hot</strong>, since it passed the Comments Threshold.'),
									'li');
				echo Wrap(T('The Hot Threads Widget and Page will now display Discussions X and Y. ' .
										'Discussion Y will appear <strong>before</strong> Discussion Y, because ' .
										'Comments have more weight in determining the "hotness" of a Discussion.'),
									'li');
				echo "</ul>\n";
			?></div>
			<ul>
				<li><?php
					echo $this->Form->Label(T('Comment Count Threshold'),
																	'Plugin.HotThreadsPlugin.CommentsThreshold');
					echo Wrap(T('This value indicates how many comments a Discussion must have ' .
											'received to be considered "hot".'),
										'div',
										array('class' => 'Info',));
					echo $this->Form->Textbox('Plugin.HotThreadsPlugin.CommentsThreshold');
				?></li>
				<li><?php
					echo $this->Form->Label(T('View Count Threshold'),
																	'Plugin.HotThreadsPlugin.ViewsThreshold');
					echo Wrap(T('This value indicates how many views a Discussion must have ' .
											'to be considered "hot".'),
										'div',
										array('class' => 'Info',));
					echo $this->Form->Textbox('Plugin.HotThreadsPlugin.ViewsThreshold');
				?></li>
			</ul>
		</fieldset>
		<?php
			echo $this->Form->Close('Save');
		?>
	</div>
	<div class="Credits">
		<h3>Credits</h3>
		<p>
			Thanks to <a href="http://vanillaforums.org/profile/38268/hgtonight" title="Zach's profile on Vanilla Community">Zach</a>, from Vanilla Community, for the ideas behind this plugin. You can see his work on <a href="http://www.daklutz.com" title="Zach's Website">his website</a>.
		</p>
	</div>
</div>
