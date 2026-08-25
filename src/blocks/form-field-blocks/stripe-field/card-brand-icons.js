/**
 * Accepted card brand icons shown inside the card number field.
 */

export function CardBrandIcons() {
	return (
		<div className="gutena-forms-stripe-payment__brand-icons" aria-hidden="true">
			<span className="gutena-forms-stripe-payment__brand-icon gutena-forms-stripe-payment__brand-icon--mastercard">
				<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="24" height="16" rx="2" fill="#fff" />
					<circle cx="9" cy="8" r="4.5" fill="#EB001B" />
					<circle cx="15" cy="8" r="4.5" fill="#F79E1B" />
					<path
						d="M12 4.82C13.08 5.78 13.8 6.78 13.8 8C13.8 9.22 13.08 10.22 12 11.18C10.92 10.22 10.2 9.22 10.2 8C10.2 6.78 10.92 5.78 12 4.82Z"
						fill="#FF5F00"
					/>
				</svg>
			</span>
			<span className="gutena-forms-stripe-payment__brand-icon gutena-forms-stripe-payment__brand-icon--visa">
				<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="24" height="16" rx="2" fill="#fff" />
					<path
						d="M9.82 10.52L10.74 5.72H12.32L11.4 10.52H9.82ZM17.48 5.84C17.14 5.72 16.58 5.6 15.88 5.6C14.12 5.6 12.92 6.52 12.9 7.72C12.88 8.56 13.68 9.04 14.28 9.32C14.9 9.62 15.1 9.8 15.1 10.06C15.08 10.46 14.62 10.66 14.18 10.66C13.52 10.66 13.16 10.54 12.68 10.32L12.48 10.22L12.26 11.44C12.66 11.6 13.36 11.72 14.08 11.74C16 11.74 17.18 10.84 17.2 9.56C17.22 8.86 16.76 8.34 15.76 7.88C15.22 7.6 14.88 7.44 14.88 7.16C14.9 6.92 15.16 6.66 15.78 6.66C16.32 6.64 16.72 6.76 17.02 6.9L17.16 6.98L17.48 5.84ZM20.2 5.72H18.88C18.48 5.72 18.16 5.84 17.98 6.28L15.56 10.52H17.26C17.26 10.52 17.56 9.76 17.64 9.54C17.86 9.54 19.72 9.54 20.04 9.54C20.1 9.82 20.26 10.52 20.26 10.52H21.74L20.2 5.72ZM7.16 5.72L5.56 8.96L5.4 8.18C5.04 7.14 4.06 6.56 2.92 6.56H2.92L2.88 6.76C4.18 7.06 5.18 7.86 5.58 8.96L6.78 10.52H8.48L10.66 5.72H7.16Z"
						fill="#172B85"
					/>
				</svg>
			</span>
			<span className="gutena-forms-stripe-payment__brand-icon gutena-forms-stripe-payment__brand-icon--amex">
				<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="24" height="16" rx="2" fill="#1F72CD" />
					<path
						d="M3.2 7.08L2.4 5.72H1.6L3.36 8.92V10.52H4.08V8.92L5.84 5.72H5.08L4.28 7.08L3.48 5.72H2.72L3.2 7.08ZM6.56 10.52H7.28V5.72H6.56V10.52ZM9.04 10.52H11.44V9.92H9.76V8.48H11.28V7.88H9.76V6.32H11.44V5.72H9.04V10.52ZM12.16 10.52H14.72L15.04 9.72H16.72L17.04 10.52H17.84L16.32 5.72H15.44L13.92 10.52H12.16ZM15.24 9.16L15.88 7.28L16.52 9.16H15.24ZM18.48 10.52H21.28C21.76 10.52 22.12 10.4 22.36 10.12C22.56 9.88 22.64 9.6 22.64 9.24V6.96C22.64 6.6 22.56 6.32 22.36 6.08C22.12 5.8 21.76 5.68 21.28 5.68H18.48V10.52ZM19.2 6.28H21.16C21.4 6.28 21.56 6.32 21.64 6.4C21.72 6.48 21.76 6.64 21.76 6.88V9.32C21.76 9.56 21.72 9.72 21.64 9.8C21.56 9.88 21.4 9.92 21.16 9.92H19.2V6.28Z"
						fill="#fff"
					/>
				</svg>
			</span>
			<span className="gutena-forms-stripe-payment__brand-icon gutena-forms-stripe-payment__brand-icon--discover">
				<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="24" height="16" rx="2" fill="#fff" />
					<rect x="0.5" y="0.5" width="23" height="15" rx="1.5" stroke="#E6E6E6" />
					<circle cx="13.5" cy="8" r="3.5" fill="#F47216" />
					<path
						d="M5.2 6.4H6.48C7.28 6.4 7.84 6.96 7.84 7.68C7.84 8.4 7.28 8.96 6.48 8.96H6.08V10.08H5.2V6.4ZM6.08 8.24H6.4C6.8 8.24 7.04 8 7.04 7.68C7.04 7.36 6.8 7.12 6.4 7.12H6.08V8.24Z"
						fill="#111"
					/>
					<path d="M8.4 6.4H9.28V10.08H8.4V6.4Z" fill="#111" />
					<path
						d="M9.84 8.24C9.84 7.2 10.64 6.32 11.84 6.32C12.08 6.32 12.28 6.36 12.48 6.4V7.28C12.28 7.2 12.04 7.16 11.84 7.16C11.28 7.16 10.8 7.64 10.8 8.24C10.8 8.84 11.28 9.32 11.84 9.32C12.04 9.32 12.28 9.28 12.48 9.2V10.08C12.28 10.12 12.08 10.16 11.84 10.16C10.64 10.16 9.84 9.28 9.84 8.24Z"
						fill="#111"
					/>
					<path
						d="M16.8 6.48C17.84 6.48 18.56 7.08 18.56 8C18.56 8.92 17.84 9.52 16.8 9.52C15.76 9.52 15.04 8.92 15.04 8C15.04 7.08 15.76 6.48 16.8 6.48ZM16.8 7.2C16.24 7.2 15.84 7.56 15.84 8C15.84 8.44 16.24 8.8 16.8 8.8C17.36 8.8 17.76 8.44 17.76 8C17.76 7.56 17.36 7.2 16.8 7.2Z"
						fill="#111"
					/>
				</svg>
			</span>
		</div>
	);
}
