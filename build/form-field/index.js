/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/form-field/edit.js":
/*!********************************!*\
  !*** ./src/form-field/edit.js ***!
  \********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ edit; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../helper */ "./src/helper.js");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _icon__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../icon */ "./src/icon.js");










//check for duplicate name attr
const isFieldNameAttrReserved = (nameAttrCheck, clientIdCheck) => {
  const blocksClientIds = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.select)('core/block-editor').getClientIdsWithDescendants();
  return (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(blocksClientIds) ? false : blocksClientIds.some(blockClientId => {
    const {
      nameAttr
    } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.select)('core/block-editor').getBlockAttributes(blockClientId);
    //different Client Id but same name attribute means duplicate
    return clientIdCheck !== blockClientId && nameAttr === nameAttrCheck;
  });
};
function edit(_ref) {
  let {
    className,
    attributes,
    setAttributes,
    isSelected,
    clientId,
    context,
    gutenaExtends = {}
  } = _ref;
  const {
    nameAttr,
    fieldName,
    fieldClasses,
    fieldType,
    isRequired,
    placeholder,
    defaultValue,
    autocomplete,
    autoCapitalize,
    textAreaRows,
    maxlength,
    minMaxStep,
    preFix,
    sufFix,
    selectOptions,
    optionsColumns,
    optionsInline,
    multiSelect,
    errorRequiredMsg,
    errorInvalidInputMsg,
    description,
    settings,
    style
  } = attributes;

  //Fields which use input tag
  const textLikeInput = ['text', 'email', 'number'];

  //Field types
  const formfieldTypes = [{
    label: 'Text',
    value: 'text'
  }, {
    label: 'Number',
    value: 'number'
  }, {
    label: 'Range',
    value: 'range'
  }, {
    label: 'TextArea',
    value: 'textarea'
  }, {
    label: 'Email',
    value: 'email'
  }, {
    label: 'Dropdown',
    value: 'select'
  }, {
    label: 'Radio',
    value: 'radio'
  }, {
    label: 'Checkbox',
    value: 'checkbox'
  }];
  let addNewFieldTypes = [];
  if ((0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__.hasFilter)('gutenaforms.field.types')) {
    addNewFieldTypes = (0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__.applyFilters)('gutenaforms.field.types', addNewFieldTypes);
  }
  const fieldTypes = (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(addNewFieldTypes) || 0 === addNewFieldTypes.length ? formfieldTypes : [...formfieldTypes, ...addNewFieldTypes];

  //get new field types
  const newFieldTypes = (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(addNewFieldTypes) || 0 === addNewFieldTypes.length ? [] : addNewFieldTypes.map(field => field.value);
  const [selectInputOption, setSelectInputOption] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(selectOptions[0]);
  const [htmlInputValue, setHtmlInputValue] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const {
    selectBlock
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.useDispatch)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.store);
  const gutenaFormClientID = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.useSelect)(select => {
    //get parent gutena form clientIds
    return select(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.store).getBlockParentsByBlockName(clientId, 'gutena/forms', true);
  }, [clientId]);

  /********************************
   Set Field Name : START
   *******************************/
  //Get Input Label from paragraph label block
  /**
   * https://developer.wordpress.org/block-editor/reference-guides/data/data-core-block-editor/#getpreviousblockclientid
   */
  //Get Input label ClientID
  const labelClientId = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.useSelect)(select => {
    let labelParaClientId = select(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.store).getAdjacentBlockClientId(clientId, -1);
    if ((0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(labelParaClientId)) {
      labelParaClientId = select(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.store).getAdjacentBlockClientId(clientId, 1);
    }
    return labelParaClientId;
  }, []);

  //Get Input label Content
  const inputLabel = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.useSelect)(select => {
    if ((0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(labelClientId)) {
      return null;
    }
    let labelAttr = select(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.store).getBlockAttributes(labelClientId);
    return (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(labelAttr) || (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(labelAttr.content) ? '' : labelAttr.content.replace(/(<([^>]+)>)|\*/gi, '').trim();
  }, [labelClientId]);

  //Use to to update block attributes using clientId
  const {
    updateBlockAttributes
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.useDispatch)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.store);

  //set name attribute if default or duplicate
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldSetNameAttr = true;
    if (shouldSetNameAttr) {
      if ('input_1' == nameAttr || '' == nameAttr || !(0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(nameAttr) && isFieldNameAttrReserved(nameAttr, clientId)) {
        for (let index = 0; index < 5000; index++) {
          let newNameAttr = 'f_' + index;
          if (!isFieldNameAttrReserved(newNameAttr, clientId)) {
            //rename label and name attribute
            setAttributes({
              nameAttr: newNameAttr
            });
            break;
          }
        }
      }
    }

    //cleanup
    return () => {
      shouldSetNameAttr = false;
    };
  }, []);

  //Prepare field name attribute: replace space with underscore and remove unwanted characters
  const prepareFieldNameAttr = fieldName => {
    fieldName = fieldName.toLowerCase().replace(/ /g, '_');
    fieldName = fieldName.replace(/\W/g, '');
    return fieldName;
  };
  const setFieldNameAttr = function (fieldName) {
    let onChange = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    //Set form field name
    setAttributes({
      fieldName
    });

    //On change from setting sidebar : set label content in label paragraph block
    if (onChange && !(0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(labelClientId)) {
      updateBlockAttributes(labelClientId, {
        content: fieldName
      });
    }
  };
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldRunInputLabel = true;
    if (shouldRunInputLabel) {
      setFieldNameAttr(inputLabel);
    }

    //cleanup
    return () => {
      shouldRunInputLabel = false;
    };
  }, [inputLabel]);

  /********************************
   Set Field Name : END
   *******************************/

  // Remove unwanted field syles
  const remove_unnecessary_styles = () => {
    //Input type range styles
    let remove_button = document.querySelector('.block-editor-block-styles__variants [aria-label="Border Style"]');
    if (!(0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(remove_button)) {
      remove_button.style.display = isSelected && 'range' === fieldType ? 'inline-block' : 'none';
    }
  };

  //Run on select block
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldRunRemoveStyle = true;
    if (shouldRunRemoveStyle) {
      // Remove unwanted field syles
      remove_unnecessary_styles();
    }

    //cleanup
    return () => {
      shouldRunRemoveStyle = false;
    };
  }, [isSelected]);

  //Save form field Classnames for gutena forms field block
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let shouldRunFieldClassnames = true;
    if (shouldRunFieldClassnames) {
      // Remove unwanted field syles
      remove_unnecessary_styles();
      let InputClassName = `gutena-forms-field ${fieldType}-field ${isRequired ? 'required-field' : ''} ${autocomplete ? 'autocomplete' : ''} `;
      if (-1 !== ['radio', 'checkbox'].indexOf(fieldType)) {
        InputClassName += optionsInline ? ' inline-options' : ' has-' + optionsColumns + '-col';
      }
      setAttributes({
        fieldClasses: InputClassName
      });
    }

    //cleanup
    return () => {
      shouldRunFieldClassnames = false;
    };
  }, [fieldType, isRequired, optionsInline, optionsColumns, autocomplete]);

  /********************************
   Input Field Component : START
   *******************************/
  const inputFieldComponent = () => {
    //Input Field
    if (0 <= textLikeInput.indexOf(fieldType)) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: fieldType,
        className: fieldClasses,
        value: htmlInputValue !== null && htmlInputValue !== void 0 ? htmlInputValue : '',
        onChange: e => setHtmlInputValue(e.target.value),
        placeholder: placeholder ? placeholder : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Placeholder…'),
        required: isRequired ? 'required' : ''
      });
    }

    //Input Field range
    if ('range' === fieldType) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "gf-range-container"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: fieldType,
        className: fieldClasses,
        required: isRequired ? 'required' : '',
        value: htmlInputValue !== null && htmlInputValue !== void 0 ? htmlInputValue : '',
        onChange: e => setHtmlInputValue(e.target.value)
      }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
        className: "gf-range-values"
      }, !(0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(minMaxStep?.min) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-prefix-value-wrapper"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-prefix"
      }, (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(preFix) ? '' : preFix), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-value"
      }, minMaxStep?.min), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-suffix"
      }, (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(sufFix) ? '' : sufFix)), !(0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(htmlInputValue) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-prefix-value-wrapper"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-prefix"
      }, (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(preFix) ? '' : preFix), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-value range-input-value"
      }, htmlInputValue), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-suffix"
      }, (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(sufFix) ? '' : sufFix)), !(0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(minMaxStep?.max) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-prefix-value-wrapper"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-prefix"
      }, (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(preFix) ? '' : preFix), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-value"
      }, minMaxStep?.max), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "gf-suffix"
      }, (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(sufFix) ? '' : sufFix))));
    }

    //Textarea Field
    if ('textarea' === fieldType) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
        className: fieldClasses,
        placeholder: placeholder ? placeholder : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Placeholder…'),
        required: isRequired ? 'required' : '',
        rows: textAreaRows
      });
    }
    if ('select' === fieldType) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
        className: fieldClasses,
        value: selectInputOption,
        onChange: e => setSelectInputOption(e.target.value),
        required: isRequired ? 'required' : ''
      }, selectOptions.map((item, index) => {
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
          key: index,
          value: item
        }, item);
      }));
    }
    if ('radio' === fieldType || 'checkbox' === fieldType) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: fieldClasses
      }, selectOptions.map((item, index) => {
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          key: index,
          className: fieldType + '-container'
        }, item, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
          type: fieldType,
          name: fieldName,
          value: item,
          checked: item === selectInputOption,
          onChange: e => setSelectInputOption(e.target.value)
        }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
          className: "checkmark"
        }));
      }));
    }
    if (!(0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(gutenaExtends?.inputFieldComponent) && 0 <= newFieldTypes.indexOf(fieldType)) {
      return gutenaExtends.inputFieldComponent();
    }
  };

  /********************************
   Input Field Component : END
   *******************************/

  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps)({
    className: `gutena-forms-${fieldType}-field field-name-${nameAttr} ${optionsInline ? 'gf-inline-content' : ''}`
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.BlockControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.ToolbarGroup, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.ToolbarButton, {
    icon: _icon__WEBPACK_IMPORTED_MODULE_7__.gutenaFormsIcon,
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Select form block', 'gutena-forms'),
    onClick: () => {
      if (!(0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(gutenaFormClientID)) {
        selectBlock(gutenaFormClientID[0]);
      }
    }
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InspectorControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Field Type', 'gutena-forms'),
    initialOpen: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.SelectControl, {
    value: fieldType,
    options: fieldTypes,
    onChange: fieldType => setAttributes({
      fieldType
    }),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Select appropriate field type for input', 'gutena-forms'),
    __nextHasNoMarginBottom: true
  }), !(0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(gutenaExtends?.gfcontrols) && gutenaExtends.gfcontrols(), -1 !== ['select', 'checkbox', 'radio'].indexOf(fieldType) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.FormTokenField, {
    label: autocomplete ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Preferences', 'gutena-forms') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Options', 'gutena-forms'),
    value: selectOptions,
    suggestions: selectOptions,
    onChange: selectOptions => setAttributes({
      selectOptions
    })
  }), -1 !== ['radio', 'checkbox'].indexOf(fieldType) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Show Inline', 'gutena-forms'),
    className: "gf-mt-1",
    help: optionsInline ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Toggle to make options show in columns', 'gutena-forms') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Toggle to make options show inline', 'gutena-forms'),
    checked: optionsInline,
    onChange: optionsInline => setAttributes({
      optionsInline
    })
  }), !optionsInline && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Columns', 'gutena-forms'),
    value: optionsColumns,
    onChange: optionsColumns => setAttributes({
      optionsColumns
    }),
    min: 1,
    max: 6,
    step: 1
  })), ('number' === fieldType || 'range' === fieldType) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", {
    className: "block-editor-block-card__title gf-mt-1 "
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Value', 'gutena-forms')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.PanelRow, {
    className: "gf-child-mb-0 gf-mb-24"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Minimum', 'gutena-forms'),
    value: minMaxStep?.min,
    type: "number",
    onChange: min => setAttributes({
      minMaxStep: {
        ...minMaxStep,
        min
      }
    })
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Maximum', 'gutena-forms'),
    value: minMaxStep?.max,
    type: "number",
    onChange: max => setAttributes({
      minMaxStep: {
        ...minMaxStep,
        max
      }
    })
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Step', 'gutena-forms'),
    value: minMaxStep?.step,
    type: "number",
    onChange: step => setAttributes({
      minMaxStep: {
        ...minMaxStep,
        step
      }
    })
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.PanelRow, {
    className: "gf-child-mb-0 gf-mb-24"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Prefix', 'gutena-forms'),
    value: preFix,
    onChange: preFix => setAttributes({
      preFix
    })
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Suffix', 'gutena-forms'),
    value: sufFix,
    onChange: sufFix => setAttributes({
      sufFix
    })
  })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Field settings', 'gutena-forms'),
    initialOpen: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Label', 'gutena-forms') + ' * ',
    className: (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(fieldName) ? ' gf-required-field' : '',
    help: (0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(fieldName) ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Please add label to the field', 'gutena-forms') : '',
    value: fieldName !== null && fieldName !== void 0 ? fieldName : '',
    onChange: fieldName => setFieldNameAttr(fieldName, true)
  }), !(0,_helper__WEBPACK_IMPORTED_MODULE_4__.gfIsEmpty)(gutenaExtends?.gfSettings) && gutenaExtends.gfSettings(), -1 !== ['text', 'textarea'].indexOf(fieldType) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Maxlength', 'gutena-forms'),
    value: maxlength,
    onChange: maxlength => setAttributes({
      maxlength
    }),
    min: 0,
    max: 500,
    step: 25
  }), 'textarea' === fieldType && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Textarea Rows', 'gutena-forms'),
    value: textAreaRows,
    onChange: textAreaRows => setAttributes({
      textAreaRows
    }),
    min: 2,
    max: 20,
    step: 1
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.PanelRow, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Placeholder', 'gutena-forms'),
    value: placeholder,
    onChange: placeholder => setAttributes({
      placeholder
    })
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.PanelRow, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_6__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Required', 'gutena-forms'),
    help: isRequired ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Toggle to make input field not required', 'gutena-forms') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Toggle to make input field required', 'gutena-forms'),
    checked: isRequired,
    onChange: isRequired => setAttributes({
      isRequired
    })
  })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", blockProps, fieldType.length > 0 ? inputFieldComponent() : ''));
}

/***/ }),

/***/ "./src/helper.js":
/*!***********************!*\
  !*** ./src/helper.js ***!
  \***********************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "gfIsEmpty": function() { return /* binding */ gfIsEmpty; },
/* harmony export */   "gfSanitizeName": function() { return /* binding */ gfSanitizeName; }
/* harmony export */ });
//Check if undefined, null, empty

const gfIsEmpty = data => {
  return 'undefined' === typeof data || null === data || '' === data;
};
const gfSanitizeName = name => {
  if (gfIsEmpty(name)) {
    name = '';
  } else {
    name = name.toLowerCase().replace(/ /g, '_');
    name = name.replace(/\W/g, '');
  }
  return name;
};

/***/ }),

/***/ "./src/icon.js":
/*!*********************!*\
  !*** ./src/icon.js ***!
  \*********************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "gutenaFormsIcon": function() { return /* binding */ gutenaFormsIcon; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);


const gutenaFormsIcon = () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Icon, {
  icon: () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    width: "24",
    height: "24",
    viewBox: "0 0 24 24",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg",
    color: "#ffffff"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    x: "2.75",
    y: "3.75",
    width: "18.5",
    height: "16.5",
    stroke: "#0EA489",
    strokeWidth: "1.5"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    x: "6",
    y: "7",
    width: "12",
    height: "1",
    fill: "#0EA489"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    x: "6",
    y: "11",
    width: "12",
    height: "1",
    fill: "#0EA489"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    x: "6",
    y: "15",
    width: "12",
    height: "1",
    fill: "#0EA489"
  }))
});

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ (function(module) {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ (function(module) {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/hooks":
/*!*******************************!*\
  !*** external ["wp","hooks"] ***!
  \*******************************/
/***/ (function(module) {

module.exports = window["wp"]["hooks"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "./src/form-field/block.json":
/*!***********************************!*\
  !*** ./src/form-field/block.json ***!
  \***********************************/
/***/ (function(module) {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"gutena/form-field","version":"1.0.0","title":"Form field","parent":["gutena/forms"],"category":"gutena","icon":"feedback","description":"Form field","attributes":{"nameAttr":{"type":"string","default":"input_1"},"fieldName":{"type":"string","default":"Name"},"fieldClasses":{"type":"string","default":""},"fieldType":{"type":"string","default":"text"},"isRequired":{"type":"boolean","default":false},"placeholder":{"type":"string","default":""},"defaultValue":{"type":"string","default":""},"autocomplete":{"type":"boolean","default":false},"autoCapitalize":{"type":"boolean","default":false},"textAreaRows":{"type":"number","default":5},"maxlength":{"type":"number","default":""},"minMaxStep":{"type":"object","default":{}},"preFix":{"type":"string","default":""},"sufFix":{"type":"string","default":""},"selectOptions":{"type":"array","default":["Big","Medium","Small"]},"optionsColumns":{"type":"number","default":1},"optionsInline":{"type":"boolean","default":false},"multiSelect":{"type":"boolean","default":false},"errorRequiredMsg":{"type":"string","default":"Field is required"},"errorInvalidInputMsg":{"type":"string","default":"Input is not valid"},"description":{"type":"string","default":""},"fieldStyle":{"type":"string","default":""},"settings":{"type":"object","default":{}}},"usesContext":["gutena-forms/formID"],"supports":{"__experimentalSettings":true,"align":["wide","full"],"color":{"background":true,"text":true},"__experimentalBorder":{"color":true,"radius":true,"style":true,"width":true,"__experimentalDefaultControls":{"color":true,"radius":true,"style":true,"width":true}},"spacing":{"margin":true,"padding":true,"blockGap":{"__experimentalDefault":"2em","sides":["horizontal","vertical"]}},"html":false},"textdomain":"gutena-forms","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!*********************************!*\
  !*** ./src/form-field/index.js ***!
  \*********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/form-field/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./block.json */ "./src/form-field/block.json");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);





const formFieldIcon = () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Icon, {
  icon: () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    width: "24",
    height: "24",
    viewBox: "0 0 24 24",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    x: "2",
    y: "4",
    width: "8",
    height: "2",
    fill: "#0EA489"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    x: "2",
    y: "11",
    width: "8",
    height: "2",
    fill: "#0EA489"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    x: "14",
    y: "4",
    width: "8",
    height: "2",
    fill: "#0EA489"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    x: "14",
    y: "11",
    width: "8",
    height: "2",
    fill: "#0EA489"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("rect", {
    x: "2",
    y: "18",
    width: "20",
    height: "2",
    fill: "#0EA489"
  }))
});
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_3__, {
  icon: formFieldIcon,
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"]
});
}();
/******/ })()
;
//# sourceMappingURL=index.js.map