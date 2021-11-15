const stylelint = require('stylelint');
const postcss = require('postcss');
const postcssSorting = require('postcss-sorting');
const { isProperty } = require('../../utils');
const checkOrder = require('./checkOrder');
const getNodeData = require('./getNodeData');
const createFlatOrder = require('./createFlatOrder');
const ruleName = require('./ruleName');
const messages = require('./messages');

module.exports = function checkNodeForOrder({
	node,
	originalNode,
	isFixEnabled,
	primaryOption,
	unspecified,
	result,
	expectedOrder,
}) {
	if (isFixEnabled) {
		let allPropertiesData = getAllPropertiesData(node);
		let shouldFixOrder = false;

		// Check if there order violation to avoid running re-ordering unnecessery
		allPropertiesData.forEach(function checkEveryPropForOrder2(propertyData, index) {
			// Skip first decl
			if (index === 0) {
				return;
			}

			// return early if we know there is a violation and auto fix should be applied
			if (shouldFixOrder) {
				return;
			}

			let previousPropertyData = allPropertiesData[index - 1];

			let checkedOrder = checkOrder({
				firstPropertyData: previousPropertyData,
				secondPropertyData: propertyData,
				unspecified,
				allPropertiesData: allPropertiesData.slice(0, index),
			});

			if (!checkedOrder.isCorrect) {
				shouldFixOrder = true;
			}
		});

		if (shouldFixOrder) {
			let sortingOptions = {
				'properties-order': createFlatOrder(primaryOption),
				'unspecified-properties-position':
					unspecified === 'ignore' ? 'bottom' : unspecified,
			};

			// creating PostCSS Root node with current node as a child,
			// so PostCSS Sorting can process it
			let tempRoot = postcss.root({ nodes: [originalNode] });

			postcssSorting(sortingOptions)(tempRoot);
		}
	}

	getAllPropertiesData(node).forEach((propertyData, index, listOfProperties) => {
		// Skip first decl
		if (index === 0) {
			return;
		}

		const previousPropertyData = listOfProperties[index - 1];

		const checkedOrder = checkOrder({
			firstPropertyData: previousPropertyData,
			secondPropertyData: propertyData,
			unspecified,
			allPropertiesData: listOfProperties.slice(0, index),
		});

		if (!checkedOrder.isCorrect) {
			const { orderData } = checkedOrder.secondNode;

			stylelint.utils.report({
				message: messages.expected(
					checkedOrder.secondNode.name,
					checkedOrder.firstNode.name,
					orderData && orderData.groupName
				),
				node: checkedOrder.secondNode.node,
				result,
				ruleName,
			});
		}
	});

	function getAllPropertiesData(inputNode) {
		return inputNode.nodes
			.filter((item) => isProperty(item))
			.map((item) => getNodeData(item, expectedOrder));
	}
};
