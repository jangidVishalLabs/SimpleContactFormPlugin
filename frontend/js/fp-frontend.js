jQuery( document ).ready(
	function ( $ ) {

		/**
		 * Initialize Select2 for role multiselect.
		 */
		$( '#fp-roles' ).select2(
			{
				ajax: {
					url: fpAjax.ajaxUrl,
					dataType: 'json',
					delay: 250,
					data: function ( params ) {
						return {
							search: params.term || '',
							action: 'fp_load_roles',
							nonce: fpAjax.nonce
						};
					},
					processResults: function ( data ) {
						return {
							results: data
						};
					}
				},
				placeholder: 'Search and select roles...',
				allowClear: true,
				width: '100%'
			}
		);

		/**
		 * Handle frontend form submission via AJAX.
		 */
		$( '#fp-frontend-form' ).on(
			'submit',
			function ( event ) {
				event.preventDefault();

				var $form    = $( this );
				var formData = $form.serialize();

				formData += '&action=fp_submit_form';

				$.post(
					fpAjax.ajaxUrl,
					formData
				)
					.done(
						function ( response ) {
							if ( response.success ) {
								Swal.fire(
									{
										icon: 'success',
										title: 'Success!',
										text: response.data
									}
								).then(
									function () {
										$form[ 0 ].reset();
										$( '#fp-roles' ).val( null ).trigger( 'change' );
									}
								);
							} else {
								Swal.fire(
									{
										icon: 'error',
										title: 'Error!',
										text: response.data
									}
								);
							}
						}
					)
					.fail(
						function () {
							Swal.fire(
								{
									icon: 'error',
									title: 'Error!',
									text: 'Network error. Please try again.'
								}
							);
						}
					);
			}
		);
	}
);
