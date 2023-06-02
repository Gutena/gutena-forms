document.addEventListener("DOMContentLoaded", function(){

	//Get parent HTML elemnet
	const getParents = ( el, query ) => {
		let parents = [];
		while ( el.parentNode !== document.body ) {
			el.matches( query ) && parents.push( el );
			el = el.parentNode;
		}
		return 0 < parents.length ? parents[0] : false ;
	};

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
	 * Update form entry status to read
	 */
	const read_entries_status_update = () => {
		let viewEl = document.querySelectorAll(
			'.gutena-forms-dashboard .quick-view-form-entry-unread'
		);
		if ( 0 < viewEl.length &&  'undefined' !== typeof gutenaFormsDashboard && null !== gutenaFormsDashboard  ) {
			for ( let i = 0; i < viewEl.length; i++ ) {
				viewEl[ i ].addEventListener( 'click', function () {
                    let entry_id = this.getAttribute('entryid');
					let table_row = getParents( this, 'tr' );
					let entry_status = false === table_row ? '' : table_row.getAttribute('currentstatus');
					//console.log("entry_status "+entry_status+" entry_id"+entry_id);
                    if ( 'undefined' !== typeof entry_status && 'unread' === entry_status && 'undefined' !== typeof entry_id && null !== entry_id && 0 < entry_id ) { 
						let el = this;
                        fetch( gutenaFormsDashboard.ajax_url, {
							method: 'POST',
							credentials: 'same-origin', // <-- make sure to include credentials
							headers:{
								'Content-Type': 'application/x-www-form-urlencoded',
								'Accept': 'application/json',
								'X-WP-Nonce' : gutenaFormsDashboard.nonce
							},
							body: new URLSearchParams({
								action:gutenaFormsDashboard.read_status_action,
								gfnonce:gutenaFormsDashboard.nonce,
							    form_entry_id:entry_id
							}),
						} )
						.then( ( response ) => response.json() )
						.then( ( response ) => {
							if ( false !== table_row ) {	
								table_row.classList.remove( 'unread' );
								table_row.classList.add( 'read' );
								table_row.setAttribute('currentstatus', 'read');
							}
						} ).catch((error) => {
							console.error('Error:', error);
							return false;
						});
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

	/**
	 * Modal : modal which encolsed modal and trigger button are under same parent 
	 */
	const gutenaFormsModal = () => {
		//Open modal
		let modalBtn = document.querySelectorAll(
			'.gutena-forms-dashboard .gutena-forms-modal-btn'
		);
		let i = 0;
		if ( 0 < modalBtn.length ) {
			for ( i = 0; i < modalBtn.length; i++ ) {
				modalBtn[ i ].addEventListener( 'click', function (e) {
					e.preventDefault();
                    let modalEl = this.parentNode.querySelector('.gutena-forms-modal');
					if ( 'undefined' !== typeof modalEl && null !== modalEl ) {
						modalEl.style.display = "block";
					}
				} );
			}
		}
		//close modal
		let closeBtn = document.querySelectorAll(
			'.gutena-forms-modal .gf-close-btn'
		);
		if ( 0 < closeBtn.length ) {
			for ( i = 0; i < closeBtn.length; i++ ) {
				closeBtn[ i ].addEventListener( 'click', function (e) {
					e.preventDefault();
					//get field group 
					let parentModalEl = getParents(
						this,
						'.gutena-forms-modal'
					);
					if ( false !== parentModalEl ) {
						parentModalEl.style.display = "none";
					}
				} );
			}
		}

		//close modal on click outside
		let modalEls = document.querySelectorAll(
			'.gutena-forms-modal'
		);
		if ( 0 < modalEls.length ) {
			window.onclick = function(event) {
				for ( i = 0; i < modalEls.length; i++ ) {
					if (event.target == modalEls[i]) {
						modalEls[i].style.display = "none";
					}
				}
			}
		}

		//Action button
		let modalActionBtn = document.querySelectorAll(
			'.gutena-forms-modal .gf-action-btn'
		);
		if ( 0 < modalActionBtn.length ) {
			for ( i = 0; i < modalActionBtn.length; i++ ) {
				modalActionBtn[ i ].addEventListener( 'click', function () {
					//action drop down 
					let dropdownEl = this.nextElementSibling;
					if (dropdownEl.style.display === "none") {
						dropdownEl.style.display = "block";
					} else {
						dropdownEl.style.display = "none";
					}
				} );
			}
		}
	};

    const ready = () => {
		select_change_url();
        confirm_delete();
		read_entries_status_update();
		gutenaFormsModal();
    }

    ready();
});