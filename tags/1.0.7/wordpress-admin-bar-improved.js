function showNav(element) {
	element.getElementsByTagName('UL')[0].style.left='auto';
	element.getElementsByTagName('A')[0].className='wpabarimp-menupop wpabarimp-menuhover';
}
function hideNav(element) {
	element.getElementsByTagName('UL')[0].style.left='-999em';
	element.getElementsByTagName('A')[0].className='wpabarimp-menupop';
}