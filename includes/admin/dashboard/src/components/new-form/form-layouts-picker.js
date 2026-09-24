import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { FORM_LAYOUTS, NEW_FORM_EDITOR_URL } from '../../utils/form-layout-constants';
import { getFormLayoutIcon } from '../../icons/form-layout-icons';

const FormLayoutsPicker = () => (
	<div className="gutena-forms-new-form__layouts">
		<p className="gutena-forms-new-form__layouts-helper">
			{ __( 'Start with a blank form and choose a column layout in the editor.', 'gutena-forms' ) }
		</p>

		<div className="gutena-forms-new-form__layouts-grid" role="list">
			{ FORM_LAYOUTS.map( ( layout ) => {
				const LayoutIcon = getFormLayoutIcon( layout.name );

				return (
					<div key={ layout.name } className="gutena-forms-new-form__layout-card" role="listitem">
						<Button
							href={ NEW_FORM_EDITOR_URL }
							className="gutena-forms-new-form__layout-button"
						>
							<span className="gutena-forms-new-form__layout-icon" aria-hidden="true">
								<LayoutIcon />
							</span>
							<span className="gutena-forms-new-form__layout-title">{ layout.title }</span>
							<span className="gutena-forms-new-form__layout-description">{ layout.description }</span>
						</Button>
					</div>
				);
			} ) }
		</div>
	</div>
);

export default FormLayoutsPicker;
