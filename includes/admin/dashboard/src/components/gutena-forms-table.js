

const GutenaFormsTable = ( { children, headers, data } ) => {

	return (
		<table
			className={ 'gutena-forms__table-wrapper' }
			cellPadding={ 0 }
			cellSpacing={ 0 }
		>
			<thead>
			{
				children && children.customHeader ? children.header( headers ) : (
					<tr>
						{ headers.map( ( header, index ) => {

							return (
								<th
									key={ index }
									style={ { width: header.width ? header.width : 'auto' } }
								>
									{ children && children.header && children.header[ header.key ] ? children.header[ header.key ]( { header, index } ) : header.value }
								</th>
							);
						} ) }
					</tr>
				)
			}
			</thead>

			<tbody>
			{
				data && data.map( ( row, index ) => {

					return (
						<tr
							key={ index }
						>
							{ headers.map( ( header, index ) => (
								<td
									key={ index }
									style={ { width: header.width ? header.width : 'auto' } }
								>
									{ children && children[ header.key ] ? children[ header.key ]( { row, index } ) : row[ header.key ] }
								</td>
							) ) }
						</tr>
					);
				} )
			}
			</tbody>

			<tfoot>
			{
				children && children.footer ? children.footer( headers ) : (
					<tr>
						{ headers.map( ( header, index ) => {

							return (
								<th
									key={ index }
									style={ { width: header.width ? header.width : 'auto' } }
								>
									{ header.value }
								</th>
							);
						} ) }
					</tr>
				)
			}
			</tfoot>
		</table>
	);
};

export default GutenaFormsTable;
