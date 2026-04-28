import variation1 from './variation-1';
import variation2 from './variation-2';
import variation3 from './variation-3';
import variation4 from './variation-4';
import allVariations from './all-variations';

const fixedVariationNames = [
	'one-column-basic',
	'one-column-modern',
	'two-column-basic',
	'two-column-modern',
];

const optionalVariations = allVariations.filter(
	( variation ) => ! fixedVariationNames.includes( variation.name )
);

const variations = [ variation1, variation2, variation3, variation4, ...optionalVariations ]
	.filter( Boolean );

export default variations;
