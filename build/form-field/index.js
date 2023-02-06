!function(){"use strict";var e=window.wp.element,t=window.wp.blocks,l=window.wp.i18n,a=window.wp.blockEditor;const n=e=>null==e||""==e;var r=window.wp.data,o=window.wp.components,i=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"gutena/form-field","version":"1.0.0","title":"Form field","ancestor":["gutena/forms"],"category":"gutena","icon":"feedback","description":"Form field","attributes":{"nameAttr":{"type":"string","default":"input_1"},"fieldName":{"type":"string","default":"Name"},"fieldClasses":{"type":"string","default":""},"fieldType":{"type":"string","default":"text"},"isRequired":{"type":"boolean","default":false},"placeholder":{"type":"string","default":""},"defaultValue":{"type":"string","default":""},"autocomplete":{"type":"boolean","default":false},"autoCapitalize":{"type":"boolean","default":false},"textAreaRows":{"type":"number","default":5},"maxlength":{"type":"number","default":""},"minMaxStep":{"type":"object","default":{}},"preFix":{"type":"string","default":""},"sufFix":{"type":"string","default":""},"selectOptions":{"type":"array","default":["Big","Medium","Small"]},"optionsColumns":{"type":"number","default":1},"optionsInline":{"type":"boolean","default":false},"multiSelect":{"type":"boolean","default":false},"errorRequiredMsg":{"type":"string","default":"Field is required"},"errorInvalidInputMsg":{"type":"string","default":"Input is not valid"},"description":{"type":"string","default":""},"settings":{"type":"object","default":{}}},"usesContext":["gutena-forms/formID"],"supports":{"__experimentalSettings":true,"align":["wide","full"],"color":{"background":true,"text":true},"__experimentalBorder":{"color":true,"radius":true,"style":true,"width":true,"__experimentalDefaultControls":{"color":true,"radius":true,"style":true,"width":true}},"spacing":{"margin":true,"padding":true,"blockGap":{"__experimentalDefault":"2em","sides":["horizontal","vertical"]}},"html":false},"textdomain":"gutena-forms","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');(0,t.registerBlockType)(i,{icon:()=>(0,e.createElement)(o.Icon,{icon:()=>(0,e.createElement)("svg",{width:"24",height:"24",viewBox:"0 0 24 24",fill:"none",xmlns:"http://www.w3.org/2000/svg"},(0,e.createElement)("rect",{x:"2",y:"4",width:"8",height:"2",fill:"#0EA489"}),(0,e.createElement)("rect",{x:"2",y:"11",width:"8",height:"2",fill:"#0EA489"}),(0,e.createElement)("rect",{x:"14",y:"4",width:"8",height:"2",fill:"#0EA489"}),(0,e.createElement)("rect",{x:"14",y:"11",width:"8",height:"2",fill:"#0EA489"}),(0,e.createElement)("rect",{x:"2",y:"18",width:"20",height:"2",fill:"#0EA489"}))}),edit:function(t){let{className:i,attributes:s,setAttributes:u,isSelected:m,clientId:c}=t;const{nameAttr:d,fieldName:p,fieldClasses:f,fieldType:g,isRequired:x,placeholder:h,defaultValue:b,autocomplete:E,autoCapitalize:_,textAreaRows:v,maxlength:y,minMaxStep:w,preFix:C,sufFix:N,selectOptions:k,optionsColumns:S,optionsInline:T,multiSelect:R,errorRequiredMsg:q,errorInvalidInputMsg:A,description:F,settings:I,style:M}=s,[P,B]=(0,e.useState)(k[0]),[O,j]=(0,e.useState)(""),D=(0,r.useSelect)((e=>{let t=e(a.store).getAdjacentBlockClientId(c,-1);return n(t)&&(t=e(a.store).getAdjacentBlockClientId(c,1)),t}),[]),V=(0,r.useSelect)((e=>{if(n(D))return null;let t=e(a.store).getBlockAttributes(D);return n(t)||n(t.content)?"":t.content.replace(/(<([^>]+)>)|\*/gi,"").trim()}),[D]),{updateBlockAttributes:$}=(0,r.useDispatch)(a.store),[z,G]=(0,e.useState)(!1);(0,e.useEffect)((()=>{"input_1"!=d&&""!=d||G(!0)}),[]);const H=e=>(e=e.toLowerCase().replace(/ /g,"_")).replace(/\W/g,""),J=function(e){let t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];z&&!n(e)?u({fieldName:e,nameAttr:H(e)}):u({fieldName:e}),t&&!n(D)&&$(D,{content:e})};(0,e.useEffect)((()=>{let e=!0;return e&&J(V),()=>{e=!1}}),[V]);const L=()=>{let e=document.querySelector('.block-editor-block-styles__variants [aria-label="Border Style"]');n(e)||(console.log("fieldType",g),e.style.display=m&&"range"===g?"inline-block":"none")};(0,e.useEffect)((()=>{let e=!0;return e&&L(),()=>{e=!1}}),[m]),(0,e.useEffect)((()=>{let e=!0;if(e){L();let e=`gutena-forms-field ${g}-field ${x?"required-field":""}`;-1!==["radio","checkbox"].indexOf(g)&&(e+=T?" inline-options":" has-"+S+"-col"),u({fieldClasses:e})}return()=>{e=!1}}),[g,x,T,S]);const W=(0,a.useBlockProps)({className:`gutena-forms-${g}-field`});return(0,e.createElement)(e.Fragment,null,(0,e.createElement)(a.InspectorControls,null,(0,e.createElement)(o.PanelBody,{title:"Form Field",initialOpen:!0},(0,e.createElement)(o.PanelRow,null,(0,e.createElement)(o.SelectControl,{label:"Field Type",value:g,options:[{label:"Text",value:"text"},{label:"Number",value:"number"},{label:"Range",value:"range"},{label:"TextArea",value:"textarea"},{label:"Email",value:"email"},{label:"Dropdown",value:"select"},{label:"Radio",value:"radio"},{label:"Checkbox",value:"checkbox"}],onChange:e=>u({fieldType:e}),help:(0,l.__)("Select appropriate field type for input","gutena-forms"),__nextHasNoMarginBottom:!0})),-1!==["select","checkbox","radio"].indexOf(g)&&(0,e.createElement)(o.FormTokenField,{label:(0,l.__)("Options","gutena-forms"),value:k,suggestions:k,onChange:e=>u({selectOptions:e})}),-1!==["radio","checkbox"].indexOf(g)&&(0,e.createElement)(e.Fragment,null,(0,e.createElement)(o.ToggleControl,{label:(0,l.__)("Show Inline","gutena-forms"),className:"gf-mt-1",help:T?(0,l.__)("Toggle to make options show in columns","gutena-forms"):(0,l.__)("Toggle to make options show inline","gutena-forms"),checked:T,onChange:e=>u({optionsInline:e})}),!T&&(0,e.createElement)(o.RangeControl,{label:(0,l.__)("Columns","gutena-forms"),value:S,onChange:e=>u({optionsColumns:e}),min:1,max:6,step:1})),-1!==["text","textarea"].indexOf(g)&&(0,e.createElement)(o.RangeControl,{label:(0,l.__)("Maxlength","gutena-forms"),value:y,onChange:e=>u({maxlength:e}),min:0,max:500,step:25}),("number"===g||"range"===g)&&(0,e.createElement)(e.Fragment,null,(0,e.createElement)("h2",{className:"block-editor-block-card__title gf-mt-1 "},(0,l.__)("Value","gutena-forms")),(0,e.createElement)(o.PanelRow,{className:"gf-child-mb-0 gf-mb-24"},(0,e.createElement)(o.TextControl,{label:(0,l.__)("Minimum","gutena-forms"),value:null==w?void 0:w.min,type:"number",onChange:e=>u({minMaxStep:{...w,min:e}})}),(0,e.createElement)(o.TextControl,{label:(0,l.__)("Maximum","gutena-forms"),value:null==w?void 0:w.max,type:"number",onChange:e=>u({minMaxStep:{...w,max:e}})}),(0,e.createElement)(o.TextControl,{label:(0,l.__)("Step","gutena-forms"),value:null==w?void 0:w.step,type:"number",onChange:e=>u({minMaxStep:{...w,step:e}})})),"range"===g&&(0,e.createElement)(o.PanelRow,{className:"gf-child-mb-0 gf-mb-24"},(0,e.createElement)(o.TextControl,{label:(0,l.__)("Prefix","gutena-forms"),value:C,onChange:e=>u({preFix:e})}),(0,e.createElement)(o.TextControl,{label:(0,l.__)("Suffix","gutena-forms"),value:N,onChange:e=>u({sufFix:e})}))),"textarea"===g&&(0,e.createElement)(o.RangeControl,{label:(0,l.__)("Textarea Rows","gutena-forms"),value:v,onChange:e=>u({textAreaRows:e}),min:2,max:20,step:1}),(0,e.createElement)(o.PanelRow,null,(0,e.createElement)(o.TextControl,{label:(0,l.__)("Field Name","gutena-forms"),value:p,onChange:e=>J(e,!0)})),(0,e.createElement)(o.PanelRow,null,(0,e.createElement)(o.TextControl,{label:(0,l.__)("Field Name Attribute","gutena-forms"),help:(0,l.__)("Contains only letters, numbers, and underscore","gutena-forms"),value:d,onChange:e=>u({nameAttr:H(e)})})),(0,e.createElement)(o.PanelRow,null,(0,e.createElement)(o.TextControl,{label:(0,l.__)("Placeholder","gutena-forms"),value:h,onChange:e=>u({placeholder:e})})),(0,e.createElement)(o.PanelRow,null,(0,e.createElement)(o.ToggleControl,{label:(0,l.__)("Required","gutena-forms"),help:x?(0,l.__)("Toggle to make input field not required","gutena-forms"):(0,l.__)("Toggle to make input field required","gutena-forms"),checked:x,onChange:e=>u({isRequired:e})})))),(0,e.createElement)("div",W,g.length>0?0<=["text","email","number","hidden","tel","url"].indexOf(g)?(0,e.createElement)("input",{type:g,className:f,placeholder:h||(0,l.__)("Placeholder…"),required:x?"required":""}):"range"===g?(0,e.createElement)("div",{className:"gf-range-container"},(0,e.createElement)("input",{type:g,className:f,required:x?"required":"",value:O,onChange:e=>j(e.target.value)}),(0,e.createElement)("p",{className:"gf-range-values"},!n(null==w?void 0:w.min)&&(0,e.createElement)("span",{className:"gf-prefix-value-wrapper"},(0,e.createElement)("span",{className:"gf-prefix"},n(C)?"":C),(0,e.createElement)("span",{className:"gf-value"},null==w?void 0:w.min),(0,e.createElement)("span",{className:"gf-suffix"},n(N)?"":N)),!n(O)&&(0,e.createElement)("span",{className:"gf-prefix-value-wrapper"},(0,e.createElement)("span",{className:"gf-prefix"},n(C)?"":C),(0,e.createElement)("span",{className:"gf-value range-input-value"},O),(0,e.createElement)("span",{className:"gf-suffix"},n(N)?"":N)),!n(null==w?void 0:w.max)&&(0,e.createElement)("span",{className:"gf-prefix-value-wrapper"},(0,e.createElement)("span",{className:"gf-prefix"},n(C)?"":C),(0,e.createElement)("span",{className:"gf-value"},null==w?void 0:w.max),(0,e.createElement)("span",{className:"gf-suffix"},n(N)?"":N)))):"textarea"===g?(0,e.createElement)("textarea",{className:f,placeholder:h||(0,l.__)("Placeholder…"),required:x?"required":"",rows:v}):"select"===g?(0,e.createElement)("select",{className:f,value:P,onChange:e=>B(e.target.value),required:x?"required":""},k.map(((t,l)=>(0,e.createElement)("option",{key:l,value:t},t)))):"radio"===g||"checkbox"===g?(0,e.createElement)("div",{className:f},k.map(((t,l)=>(0,e.createElement)("label",{key:l,className:g+"-container"},t,(0,e.createElement)("input",{type:g,name:p,value:t,checked:t===P,onChange:e=>B(e.target.value)}),(0,e.createElement)("span",{className:"checkmark"}))))):void 0:""))}})}();