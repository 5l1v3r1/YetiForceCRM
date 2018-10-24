{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Chat -->
	{function ITEM_USER CLASS=''}
		<li class="js-item-user c-chat__user-item {$CLASS}" data-user-id="{$USER['user_id']}" data-js="data">
			<div class="row">
				<div class="col-3 author">
					{assign var=IMAGE value=$USER['image']}
					{assign var=IS_IMAGE value=isset($IMAGE['url'])}
					<img src="{if $IS_IMAGE}{$IMAGE['url']}{/if}" class="mr-2{if !$IS_IMAGE} hide{/if}"
						 alt="{$USER['user_name']}"
						 title="{$USER['user_name']}"/>
					<span class="fas fa-user userImage{if $IS_IMAGE} hide{/if}"></span>


					{*{if $IMAGE}
						<img src="{$IMAGE.url}" class="mr-2" alt="{$USER['user_name']}"
							 title="{$USER['user_name']}"
							 height="80" align="left">
					{else}
						<span class="fas fa-user userImage"></span>
					{/if}*}
				</div>
				<div class="col-9">
					<div class="row js-user-name">{$USER['user_name']}</div>
					<div class="row js-role font-weight-bold color-blue-600">{$USER['role_name']}</div>
					<div class="row js-message text-truncate">{$USER['message']}</div>
				</div>
			</div>
		</li>
	{/function}
	<div class="row chat-view-container">
		<div class="col-9">
			<div class="row">
				<input type="text" class="form-control message js-search-message"{' '} autocomplete="off"{' '}
					   placeholder="{\App\Language::translate('LBL_SEARCH_MESSAGE', $MODULE_NAME)}" data-js="keydown"/>
			</div>
			<div class="d-flex flex-column js-chat-main-content">
				<div class="row d-flex flex-grow-1">
					<div class="col-10 js-chat_content h-100 w-100 mb-4"
						 data-current-room-type="{$CURRENT_ROOM['roomType']}"
						 data-current-record-id="{$CURRENT_ROOM['recordId']}"
						 data-message-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}"
						 data-room-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}"
						 data-max-length-message="{AppConfig::module('Chat', 'MAX_LENGTH_MESSAGE')}"
						 data-js="append">
						{include file=\App\Layout::getTemplatePath('Entries.tpl', 'Chat')}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="input-group">
					<div class="input-group-prepend">
							<span class="input-group-text">
								<span class="fas fa-comments"></span>
							</span>
					</div>
					<textarea class="form-control noresize message js-chat-message" rows="2" autocomplete="off"
							  placeholder="{\App\Language::translate('LBL_MESSAGE', $MODULE_NAME)}"
							  data-js="keydown">
					</textarea>
				</div>
				<button type="button" class="btn btn-primary js-btn-send" data-js="click">
					<span class="fas fa-comment"></span>
				</button>
			</div>
		</div>
		<div class="col-3">
			<div>
				<input type="text" class="form-control message js-search-participants" autocomplete="off"
					   placeholder="{\App\Language::translate('LBL_SEARCH_PARTICIPANTS', $MODULE_NAME)}"
					   data-js="keydown"/>
			</div>
			<h5>{\App\Language::translate('LBL_PARTICIPANTS', $MODULE_NAME)}</h5>
			<div class="js-participants-list" data-js="container">
				{ITEM_USER USER=['user_id'=>'', 'user_name'=>'', 'image'=>null] CLASS='js-temp-item-user hide'}
				<ul class="js-users" data-js="container">
					{foreach item=USER from=$PARTICIPANTS}
						{ITEM_USER USER=$USER}
					{/foreach}
				</ul>
			</div>
		</div>
	</div>
	<!-- /tpl-Chat-Chat -->
{/strip}