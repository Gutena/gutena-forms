

const GutenaFormsTable = ( { children, headers, data, name, tourPlaceholderRow = null } ) => {

	const tourTableTarget = 'entries' === name ? 'entries-table' : null;

	return (
		<div
			className={ 'gutena-forms__table' }
			id={ `gutena-forms__${ name }-table` }
			{ ...( tourTableTarget ? { 'data-tour': tourTableTarget } : {} ) }
		>
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

								let Element = children && children.header && children.header[ header.key ];
								return (
									<th
										key={ index }
										style={ { width: header.width ? header.width : 'auto' } }
									>
										{
											Element ? Element( { header, index } ) : header.value
										}
									</th>
								);
							} ) }
						</tr>
					)
				}
				</thead>

				<tbody>
				{
					tourPlaceholderRow && (
						<tr
							className="gutena-forms-tour__entries-placeholder-row"
							data-tour="entries-row-placeholder"
						>
							{ headers.map( ( header, headerIndex ) => {
								const placeholderValue = tourPlaceholderRow[ header.key ];

								return (
									<td
										key={ `tour-placeholder-${ headerIndex }` }
										style={ { width: header.width ? header.width : 'auto' } }
									>
										{ placeholderValue || '' }
									</td>
								);
							} ) }
						</tr>
					)
				}
				{
					data && data.map( ( row, index ) => {

						return (
							<tr
								key={ index }
								{ ...( tourTableTarget && 0 === index ? { 'data-tour': 'entries-row' } : {} ) }
							>
								{ headers.map( ( header, index ) => {
									let Element = children && children.body && children.body[ header.key ];

									return (
										<td
											key={ index }
											style={ { width: header.width ? header.width : 'auto' } }
										>
											{
												Element ? Element( { row, index, header } ) : (
													row[ header.key ] ? row[ header.key ] : <span style={{ textDecoration: "underline", color: '#ef5555' }}>Field not found</span>
												) }
										</td>
									);
								} ) }
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
		</div>
	);
};

export default GutenaFormsTable;
