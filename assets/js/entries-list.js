document.addEventListener("DOMContentLoaded", function(){

	/**
	 * On select url will be be change and perform a GET request
	 */
	const select_change_url = () => {
		let selectEl = document.querySelectorAll(
			'.gutena-forms-dashboard .select-change-url'
		);
		if ( 0 < selectEl.length ) {
			for ( let i = 0; i < selectEl.length; i++ ) {
				selectEl[ i ].addEventListener( 'change', function () {
                    let url = this.getAttribute('url');
                    if ( 'undefined' !== typeof url && null !== url ) {
                        window.location = url+( this.value );
                    }
				} );
			}
		}
	}

	/**
	 * On click delete link. it will trigger a confirmation box and perform a GET delete action accoedingly 
	 */
    const confirm_delete = () => {
		let delete_entries = document.querySelectorAll(
			'.gutena-forms-dashboard .gf-delete'
		);
		if ( 0 < delete_entries.length ) {
			for ( let i = 0; i < delete_entries.length; i++ ) {
				delete_entries[ i ].addEventListener( 'click', function (e) {
					e.preventDefault();
                    let url = this.getAttribute('href');
                    if ( 'undefined' !== typeof url && null !== url && window.confirm("Do you really want to delete this entry?") ) {
                        window.location = url;
                    }
				} );
			}
		}
	};

    const ready = () => {
		select_change_url();
        confirm_delete();
    }

    ready();
});