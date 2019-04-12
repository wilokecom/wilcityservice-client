(function ($) {
	'use strict';

	$(document).ready(function () {
		let $bulkUpdateAction = $('#wilcity-updates-wrapper'),
			$msg = null,
			$updatePlugins = $('.wil-update-plugin'),
			$document = $(document);

		function reUpdateResponse(type) {
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action: 'wiloke_reupdate_response_of_'+type
				}
			})
		}

		function showErrorMsg(msg) {
			$msg.html(msg);
			$msg.addClass('error positive');
			$msg.removeClass('hidden');
		}

		function showSuccessMsg(msg) {
			$msg.html(msg);
			$msg.addClass('success positive');
			$msg.removeClass('hidden');
		}

		function hideMsg() {
			$msg.removeClass('hidden');
			$msg.addClass('hidden');
		}

		updatePlugins();
		function updatePlugins(){
			$bulkUpdateAction.on( 'click', '.wil-update-plugin', function( event ) {
				let $btn   = $( event.target ),
					$card   = $btn.closest('.wil-plugin-wrapper'),
					$currentVer = $card.find('.wil-current-version'),
					$newVer = $card.find('.wil-new-version'),
					$buttonRow = $btn.parents( '.wil-button-wrapper' );

				$msg = $('#wilcity-update-plugins').find('.wil-plugin-update-msg');

				event.preventDefault();

				if (  $btn.hasClass( 'disable' ) ) {
					return;
				}
				$updatePlugins.prop('disabled', true);
				$card.addClass('ui form loading');

				wp.updates.maybeRequestFilesystemCredentials( event );

				hideMsg();

				let oStatus = wp.updates.ajax( 'update-plugin', {
					plugin: $buttonRow.data( 'plugin' ),
					slug:   $buttonRow.data( 'slug' )
				} );

				oStatus.fail(response=>{
					showErrorMsg(response.errorMessage + ' Please try to click Refresh button then click on Update button again');
					$card.removeClass('ui form loading');
					$updatePlugins.prop('disabled', false);
					reUpdateResponse('plugins');
				});

				oStatus.done(response=>{
					$currentVer.html($newVer.html());
					$card.removeClass('ui form loading');
					$updatePlugins.prop('disabled', false);
					reUpdateResponse('plugins');
					showSuccessMsg('Congratulations! This plugin has been updated successfully');
					$btn.parent().remove();
				});

			} );
		}

		updateTheme();
		function updateTheme(){
			$bulkUpdateAction.on('click', '.wil-update-theme', function(event){
				let $btn   = $(event.target),
					$card   = $btn.closest('.wil-theme-item-wrapper'),
					$currentVer = $card.find('.wil-current-version'),
					$newVer = $card.find('.wil-new-version'),
					$buttonRow = $btn.parents( '.wil-button-wrapper' );
				$msg = $('#wilcity-update-theme').find('.wil-plugin-update-msg');

				event.preventDefault();
				if (  $btn.hasClass( 'disable' ) ) {
					return;
				}

				$card.addClass('ui form loading');
				hideMsg();

				let oArgs = _.extend( {
					success: '',
					error: '',
					slug: $buttonRow.data('slug')
				});

				let oStatus = wp.updates.updateTheme(oArgs);

				oStatus.fail(response=>{
					showErrorMsg(response.errorMessage);
					$card.removeClass('ui form loading');
					reUpdateResponse('theme');
				});

				oStatus.done(response=>{
					$currentVer.html($newVer.html());
					$card.removeClass('ui form loading');
					reUpdateResponse('plugins');
					showSuccessMsg('Congratulations! This plugin has been updated successfully');
				});
			})
		}
	});

})(jQuery);