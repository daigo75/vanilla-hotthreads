<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

// File hotthreads.defines.php must be included by manually specifying the whole
// path. It will then define some shortcuts for commonly used paths.
require(PATH_PLUGINS . '/HotThreads/lib/hotthreads.defines.php');

// Define the plugin:
$PluginInfo['HotThreads'] = array(
	'Name' => 'Hot Threads',
	'Description' => 'Displays a list of "hot" discussions, i.e. the ones with most views and/or comments.',
	'Version' => '13.03.07',
	'RequiredApplications' => array('Vanilla' => '2.0'),
	'RequiredTheme' => FALSE,
	'HasLocale' => FALSE,
	'MobileFriendly' => TRUE,
	'SettingsUrl' => '/plugin/hotthreads',
	'SettingsPermission' => 'Garden.Settings.Manage',
	'Author' => 'D.Zanella',
	'AuthorEmail' => 'diego@pathtoenlightenment.net',
	'AuthorUrl' => 'http://dev.pathtoenlightenment.net',
	'RegisterPermissions' => array('Plugins.HotThreads.Manage',),
);

/**
 * Displays a Widget and a Page with a list of Hot Threads, based on their View
 * Count and their Comment Count.
 */
class HotThreadsPlugin extends Gdn_Plugin {
	/**
	 * Class constructor.
	 *
	 * @return HotThreadsPlugin.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Given the name of a Page Set, it returns all the pages included into it.
	 * It's mainly used internally, to determine if the Hot Threads Widget should
	 * be displayed, based on the Controller that handles the requests.
	 *
	 * @param string PageSetName The name of the Page Set to retrieve.
	 * @return array An array containing a list of the pages included in the Page
	 * Set.
	 */
	private function GetDisplayPages($PageSetName = HOTTHREADS_PAGESET_ALL) {
		$PageSets = array(
			HOTTHREADS_PAGESET_DISCUSSIONS => array(
				// Note that the two lines below are not duplicate. One is "discussion",
				// the other is discussionS (plural)
				'discussioncontroller',
				'discussionscontroller',
				'categoriescontroller',
			),
			HOTTHREADS_PAGESET_ANNOUNCEMENTS => array(
				'profilecontroller',
				'activitycontroller'
			)
		);

		// Produce an "all" entry, containing all the pages of the other pagesets
		$PageSets[HOTTHREADS_PAGESET_ALL] = array_merge($PageSets[HOTTHREADS_PAGESET_DISCUSSIONS],
																										$PageSets[HOTTHREADS_PAGESET_ANNOUNCEMENTS]);

		return GetValue($PageSetName, $PageSets);
	}


	/**
	 * Determines if the Hot Threads widget should be displayed, by checking if
	 * the requesting Controller matches the pages where the widget was enabled.
	 *
 	 * @param Controller Sender Sending controller instance.
 	 * @return bool True, if the Widget should be displayed, False otherwise.
 	 */
	private function ShouldDisplayWidget($Sender) {
		// Get the config and controller name for comparison
		$DisplayPageSet = C('Plugin.HotThreads.DisplayPageSet', HOTTHREADS_PAGESET_ALL);

		return InArrayI($Sender->ControllerName, $this->GetDisplayPages($DisplayPageSet));
	}

	// Create a method called "HotThreadsPlugin" on the PluginController
   public function PluginController_HotThreads_Create($Sender) {
		$Sender->Title('Hot Threads Plugin');
		$Sender->AddSideMenu('plugin/hotthreadslist');

		// Prepare a form to be used by call handlers
		$Sender->Form = new Gdn_Form();
		$this->Dispatch($Sender, $Sender->RequestArgs);
	}

	/**
	 * Renders the Hot Threads page.
	 * This method is an almost exact copy of DiscussionController::Index(), with
	 * the exception that it doesn't load Announcements. Also, it loads only
	 * Discussions with a certain amount of Views or Comments.
	 *
 	 * @param Controller Sender Sending controller instance.
	 * @param int Page The page to display (used by Pager).
	 */
	private function ShowHotThreads($Sender, $Page = '0') {
		// Determine offset from $Page
		list($Page, $Limit) = OffsetLimit($Page, C('Vanilla.Discussions.PerPage', 30));
		$Sender->CanonicalUrl(Url(ConcatSep('/', 'discussions', PageNumber($Page, $Limit, TRUE, FALSE)), TRUE));

		// Validate $Page
		if (!is_numeric($Page) || $Page < 0) {
			$Page = 0;
		}

		// Setup head.
		if (!$Sender->Data('Title')) {
			$Sender->Title(T('Hot Discussions'));
		}

		// Add modules
		$Sender->AddModule('NewDiscussionModule');
		$Sender->AddModule('CategoriesModule');
		$Sender->AddModule('BookmarkedModule');

		// Set criteria & get discussions data
		$Sender->SetData('Category', FALSE, TRUE);
		$DiscussionModel = new DiscussionModel();
		$DiscussionModel->Watching = TRUE;


		/* Half a day is added to the Age Threshold because we want to give maximum
		 * priority to all the Discussion whose age is less than, or equal to the
		 * configured value. Due to the calculation performed to determine priority,
		 * a Discussion whose age is equal to the threshold would get a priority of
		 * "1", rather than the correct "0".
		 * By adding half a day, the calculation will produce a result which is less
		 * than 1, giving the Discussions the correct priority.
		 */
		$AgeThreshold = C('Plugin.HotThreads.AgeThreshold', HOTTHREADS_DEFAULT_AGETHRESHOLD);
		$AgeThreshold = (int)$AgeThreshold + 0.5;

		// Filter the Discussions, keeping only the ones with a certain amount of
		// Views or Comments
		$DiscussionModel->SQL
			->Select('FLOOR((TO_DAYS(NOW())-TO_DAYS(d.DateLastComment))/' . $AgeThreshold . ')', '', 'AgeWeight')
			->Select('(TO_DAYS(NOW())-TO_DAYS(d.DateLastComment))', '', 'Age')
			->BeginWhereGroup()
			->Where('d.CountViews >=', C('Plugin.HotThreads.ViewsThreshold',
																	 HOTTHREADS_DEFAULT_VIEWSTHRESHOLD))
			->OrWhere('d.CountComments >=', C('Plugin.HotThreads.CommentsThreshold',
																				HOTTHREADS_DEFAULT_COMMENTSTHRESHOLD))
			->EndWhereGroup()
			->OrderBy('AgeWeight', 'asc')
			->OrderBy('d.CountComments', 'desc')
			->OrderBy('d.CountViews', 'desc');

		// Get Discussions
		$Sender->DiscussionData = $DiscussionModel->Get($Page, $Limit);

		// Get Discussion Count
		$CountDiscussions = $Sender->DiscussionData->NumRows();
		$Sender->SetData('CountDiscussions', $CountDiscussions);

//		var_dump($Sender->DiscussionData);
//		die();

		$Sender->SetData('Discussions', $Sender->DiscussionData, TRUE);
		$Sender->SetJson('Loading', $Page . ' to ' . $Limit);

		// Build a pager
		$PagerFactory = new Gdn_PagerFactory();
		$Sender->EventArguments['PagerType'] = 'Pager';
		$Sender->FireEvent('BeforeBuildPager');
		$Sender->Pager = $PagerFactory->GetPager($Sender->EventArguments['PagerType'], $this);
		$Sender->Pager->ClientID = 'Pager';
		$Sender->Pager->Configure(
			$Page,
			$Limit,
			$CountDiscussions,
			'discussions/%1$s'
		);
		$Sender->SetData('_PagerUrl', 'discussions/hotthreads/{Page}');
		$Sender->SetData('_Page', $Page);
		$Sender->SetData('_Limit', $Limit);
		$Sender->FireEvent('AfterBuildPager');

		// Deliver JSON data if necessary
		if($Sender->DeliveryType() != DELIVERY_TYPE_ALL) {
			$Sender->SetJson('LessRow', $Sender->Pager->ToString('less'));
			$Sender->SetJson('MoreRow', $Sender->Pager->ToString('more'));
			$Sender->View = 'discussions';
		}

		// Set a definition of the user's current timezone from the db. jQuery
		// will pick this up, compare to the browser, and update the user's
		// timezone if necessary.
		$CurrentUser = Gdn::Session()->User;
		if (is_object($CurrentUser)) {
			$ClientHour = $CurrentUser->HourOffset + date('G', time());
			$Sender->AddDefinition('SetClientHour', $ClientHour);
		}

		// Render default view (discussions/index.php)
		$Sender->View = 'index';
		$Sender->Render();
	}

	/**
	 * Loads and configures the Hot Threads module, which will generate the HTML
	 * for the Hot Threads widget in the Sidebar.
	 *
 	 * @param Controller Sender Sending controller instance.
 	 * @return HotThreadsListModule An instance of the module.
 	 */
	private function LoadHotThreadsModule($Sender) {
		// Include Hot Threads List module file
		//include_once(HOTTHREADS_PLUGIN_MODULES_PATH . '/class.hotthreadslist.module.php');

		$HotThreadsPluginModule = new HotThreadsListModule($Sender);
		$HotThreadsPluginModule->LoadData(
			C('Plugin.HotThreads.MaxEntriesToDisplay'),
			C('Plugin.HotThreads.ViewsThreshold'),
			C('Plugin.HotThreads.CommentsThreshold'),
			C('Plugin.HotThreads.AgeThreshold')
		);
		return $HotThreadsPluginModule;
	}

	/**
	 * Handler of global "Render_Before" event.
	 * It initialises and renders the module displaying the hot threads.
	 *
   * @param Controller Sender Sending controller instance.
	 */
	public function Base_Render_Before($Sender) {
		$Sender->AddCssFile('hotthreads.css', 'plugins/HotThreads/design/css');

		if(!$this->ShouldDisplayWidget($Sender)) {
			return;
		}

		// TODO Read configuration to load multiple Discussion list widgets in a specific order

		// Load the module that will render the Hot Threads widget and add it to the
		// modules list
		$HotThreadsPluginModule = $this->LoadHotThreadsModule($Sender);
		$Sender->AddModule($HotThreadsPluginModule);

		// If Auto Update is enabled (delay greater than zero), load and configure
		// the related JavaScript file
		if(($AutoUpdateDelay = C('Plugin.HotThreads.AutoUpdateDelay', HOTTHREADS_DEFAULT_AUTOUPDATEDELAY)) > 0) {
			$this->LoadWidgetAutoRefreshScript($Sender, $AutoUpdateDelay);
		}
	}

	/**
	 * Loads and configures the JavaScript file containing the script that will
	 * automatically refresh the Hot Threads Widget.
	 *
   * @param Controller Sender Sending controller instance.
	 * @param int AutoUpdateDelay The delay between each refresh, in seconds.
	 */
	protected function LoadWidgetAutoRefreshScript($Sender, $AutoUpdateDelay = HOTTHREADS_DEFAULT_AUTOUPDATEDELAY) {
		// Load the JS file that will handle Widget's auto-refresh
		$Sender->AddJsFile('hotthreadswidget.js', 'plugins/HotThreads/js');

		// Expose the Auto Update interval to the front-end, so that it can be used
		// by the JS script
		$Sender->AddDefinition('HotThreadsWidget_AutoUpdateDelay', $AutoUpdateDelay);
	}

	/**
	 * Renders the HTML produced by the Hot Threads Module. This method is mainly
	 * used to updated the widget "on the fly" via Ajax calls.
	 */
	public function Controller_GetWidgetContent($Sender) {
		// TODO Parse Request to determine which data to load and display in the widget

		// Load the module that will render the Hot Threads widget and output the
		// HTML it generates
		$HotThreadsPluginModule = $this->LoadHotThreadsModule($Sender);
		echo $HotThreadsPluginModule->RenderListItems();
	}

	/**
	 * Add Controller to display Hot Threads.
	 *
	 * @param Controller Sender Sending controller instance.
	 */
	public function DiscussionsController_HotThreads_Create($Sender) {
		// Replace standard View with the "Hot Threads" view
		$this->ShowHotThreads($Sender, GetValue(0, $Args, 'p1'));
	}

	/**
	 * Vanilla 2.0 Event Handler.
	 * Adds a "Hot Threads" link to tabs in Index page.
	 *
	 * @param Controller Sender Sending controller instance.
	 */
	public function Base_AfterDiscussionTabs_Handler($Sender) {
		// TODO Review code to display a Sprite in Vanilla 2.1
		$CssClass = $Sender->RequestMethod == 'hotthreads' ? 'Active' : '';
		echo Wrap(Anchor(T('Hot'),
										 '/discussions/hotthreads',
										 'TabLink'),
							'li',
							array('class' => 'HotThreads ' . $CssClass)
						 );
	}

	/**
	 * Vanilla 2.1 Event Handler.
	 * Calls HotThreadsPlugin::Base_AfterDiscussionTabs_Handler().
	 *
	 * @see HotThreadsPlugin::Base_AfterDiscussionTabs_Handler().
	 */
	public function DiscussionsController_AfterDiscussionFilters_Handler($Sender) {
		return $this->Base_AfterDiscussionTabs_Handler($Sender);
	}

	/**
	 * Vanilla 2.1 Event Handler.
	 * Calls HotThreadsPlugin::Base_AfterDiscussionTabs_Handler().
	 *
	 * @see HotThreadsPlugin::Base_AfterDiscussionTabs_Handler().
	 */
	public function DiscussionController_AfterDiscussionFilters_Handler($Sender) {
		return $this->Base_AfterDiscussionTabs_Handler($Sender);
	}

	/**
	 * Renders the default page.
	 *
   * @param Controller Sender Sending controller instance.
	 */
	public function Controller_Index($Sender) {
		$this->Controller_Settings($Sender);
	}

	/**
	 * Set validation rules for Plugin's configuration settings.
	 *
	 * @param Gdn_Validation Validation The Validation object to which the rules
	 * will be added.
	 */
	private function _SetConfigValidationRules(Gdn_Validation $Validation) {
		$Validation->ApplyRule('Plugin.HotThreads.DisplayPageSet', 'Required');

		$Validation->ApplyRule('Plugin.HotThreads.AutoUpdateDelay', 'Required');
		$Validation->ApplyRule('Plugin.HotThreads.AutoUpdateDelay', 'Integer');

		$Validation->ApplyRule('Plugin.HotThreads.MaxEntriesToDisplay', 'Required');
		$Validation->ApplyRule('Plugin.HotThreads.MaxEntriesToDisplay', 'Integer');

		// Thresholds
		$Validation->ApplyRule('Plugin.HotThreads.AgeThreshold', 'Required');
		$Validation->ApplyRule('Plugin.HotThreads.AgeThreshold', 'Integer');
		$Validation->ApplyRule('Plugin.HotThreads.ViewsThreshold', 'Required');
		$Validation->ApplyRule('Plugin.HotThreads.ViewsThreshold', 'Integer');
		$Validation->ApplyRule('Plugin.HotThreads.CommentsThreshold', 'Required');
		$Validation->ApplyRule('Plugin.HotThreads.CommentsThreshold', 'Integer');
	}

	/**
	 * Renders the Settings page.
	 *
   * @param Controller Sender Sending controller instance.
	 */
	public function Controller_Settings($Sender) {
		$Sender->Permission('Vanilla.Settings.Manage');
		$Sender->SetData('PluginDescription',$this->GetPluginKey('Description'));

		$Validation = new Gdn_Validation();
		$ConfigurationModel = new Gdn_ConfigurationModel($Validation);
		$ConfigurationModel->SetField(array(
			'Plugin.HotThreads.HideIfEmpty' => HOTTHREADS_DEFAULT_HIDEIFEMPTY,
			'Plugin.HotThreads.DisplayPageSet' => HOTTHREADS_PAGESET_ALL,
			'Plugin.HotThreads.AutoUpdateDelay'	=> HOTTHREADS_DEFAULT_AUTOUPDATEDELAY,
			'Plugin.HotThreads.MaxEntriesToDisplay'	=> HOTTHREADS_DEFAULT_MAXENTRIES,
			'Plugin.HotThreads.AgeThreshold'	=> HOTTHREADS_DEFAULT_AGETHRESHOLD,
			'Plugin.HotThreads.ViewsThreshold'	=> HOTTHREADS_DEFAULT_VIEWSTHRESHOLD,
			'Plugin.HotThreads.CommentsThreshold'	=> HOTTHREADS_DEFAULT_COMMENTSTHRESHOLD
		));

		// Set the model on the form.
		$Sender->Form->SetModel($ConfigurationModel);

		// If seeing the form for the first time...
		if($Sender->Form->AuthenticatedPostBack() === FALSE) {
			// Apply the config settings to the form.
			$Sender->Form->SetData($ConfigurationModel->Data);
		}
		else {
			// Set validation rules for configuration parameters
			$this->_SetConfigValidationRules($ConfigurationModel->Validation);

			$Saved = $Sender->Form->Save();

			if($Saved) {
				$Sender->InformMessage('<span class="InformSprite Sliders"></span>'.T("Your changes have been saved."),'HasSprite');
			}
		}

		// Render the Settings view
		$Sender->Render($this->GetView('hotthreads_generalsettings_view.php'));
	}

	/**
	 * Add a link to Plugin's configuration page to the dashboard menu.
	 *
   * @param Controller Sender Sending controller instance.
	 */
	public function Base_GetAppSettingsMenuItems_Handler($Sender) {
		$Menu = $Sender->EventArguments['SideMenu'];
		$Menu->AddLink('Add-ons', T('Hot Threads'), 'plugin/hotthreads', 'Garden.Settings.Manage');
	}

	/**
	 * Performs the initial Setup when the plugin is enabled.
	 */
	public function Setup() {
		// Set Plugin's default settings
		SaveToConfig('Plugin.HotThreads.HideIfEmpty', HOTTHREADS_DEFAULT_HIDEIFEMPTY);
		SaveToConfig('Plugin.HotThreads.AutoUpdateDelay', HOTTHREADS_DEFAULT_AUTOUPDATEDELAY);
		SaveToConfig('Plugin.HotThreads.MaxEntriesToDisplay', HOTTHREADS_DEFAULT_MAXENTRIES);
		SaveToConfig('Plugin.HotThreads.DisplayPageSet', HOTTHREADS_PAGESET_ALL);
		SaveToConfig('Plugin.HotThreads.AgeThreshold', HOTTHREADS_DEFAULT_AGETHRESHOLD);
		SaveToConfig('Plugin.HotThreads.ViewsThreshold', HOTTHREADS_DEFAULT_VIEWSTHRESHOLD);
		SaveToConfig('Plugin.HotThreads.CommentsThreshold', HOTTHREADS_DEFAULT_COMMENTSTHRESHOLD);
	}

	/**
	 * Performs a cleanup when the plugin is removed.
	 */
	public function Cleanup() {
		// Remove Plugin's settings
		RemoveFromConfig('Plugin.HotThreads.HideIfEmpty');
		RemoveFromConfig('Plugin.HotThreads.AutoUpdateDelay');
		RemoveFromConfig('Plugin.HotThreads.MaxEntriesToDisplay');
		RemoveFromConfig('Plugin.HotThreads.DisplayPageSet');
		RemoveFromConfig('Plugin.HotThreads.AgeThreshold');
		RemoveFromConfig('Plugin.HotThreads.ViewsThreshold');
		RemoveFromConfig('Plugin.HotThreads.CommentsThreshold');
	}
}
