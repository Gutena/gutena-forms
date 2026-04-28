import allVariations from './all-variations';

export default function getVariationByName( name ) {
	return allVariations.find( ( variation ) => variation.name === name );
}
