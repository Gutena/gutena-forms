import { Icon } from '@wordpress/components';
const FieldGroupIcon = () => {
	return (
		<>
			<Icon
				icon={ () => (
					<svg
						width="24"
						height="24"
						viewBox="0 0 24 24"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
						color="#ffffff"
					>
						<rect
							x="13.75"
							y="10.75"
							width="7.5"
							height="9.5"
							stroke="#0EA489"
							strokeWidth="1.5"
						/>
						<rect x="2" y="4" width="20" height="2" fill="#0EA489" />
						<rect x="2" y="11" width="9" height="2" fill="#0EA489" />
						<rect x="2" y="18" width="7" height="2" fill="#0EA489" />
					</svg>
				) }
			/>
		</>
	);
};

export default FieldGroupIcon;
