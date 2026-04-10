import { useState, useEffect } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { NavLink } from 'react-router';
import Crown from '../icons/crown';
import { gutenaFormsFetchMenus } from '../api';
import { gutenaFormsStrContains } from '../utils/functions';

const GutenaFormsHeader = ({ activeMenu, setActiveMenu }) => {

	const [menus, setMenus] = useState(false);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		setLoading(true);
		gutenaFormsFetchMenus()
			.then((response) => {
				setMenus(response);
				setLoading(false);
			});
	}, []);

	return (

		<div className={'gutena-forms__header-container'}>
			<div className={'gutena-forms_logo'}>
				<NavLink
					to={'/settings/dashboard'}
				>
					<img
						src={`${gutenaFormsAdmin.pluginURL}assets/img/logo.png`}
						alt={'Gutena Forms Logo'}
					/>
				</NavLink>
			</div>
			<div className='nav-menu-container'>
				{!loading && menus && (
					<nav className={'gutena-forms__header-menu'}>
						<div className='mobile_toggle'>
							<svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-iguwhy" focusable="false" aria-hidden="true" viewBox="0 0 24 24"><path d="M3 18h18v-2H3zm0-5h18v-2H3zm0-7v2h18V6z"></path></svg>
						</div>
						<ul>
							{menus.map((menu, index) => {
								if (menu.external) {
									return (
										<li key={index}>
											<a
												href={menu.slug}
												target="_blank"
												rel="noopener noreferrer"
											>{menu.title}</a>
										</li>
									);
								} else {
									return (
										<li key={index}>
											<NavLink
												to={`settings${menu.slug}`}
												className={gutenaFormsStrContains(menu.slug, activeMenu) ? 'active' : ''}
												onClick={() => setActiveMenu(menu.slug)}
											>
												{menu.title}
											</NavLink>
										</li>
									);
								}
							})}
						</ul>
					</nav>
				)}
				<div className='cta-top'>
					{
						!gutenaFormsAdmin.hasPro && (
							<Button
								className={'gutena-forms__upgrade-button'}
								variant="primary"
								href="https://gutenaforms.com/pricing/?utm_source=plugin_dashboard&utm_medium=website&utm_campaign=free_plugin"
								target="_blank"
								rel="noopener noreferrer"
							>
								<Crown />
								{__('Upgrade Now', 'gutena-froms')}
							</Button>
						)
					}
				</div>
			</div>

		</div>
	);
};

export default GutenaFormsHeader;
