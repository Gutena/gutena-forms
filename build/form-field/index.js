!function(){"use strict";var e=window.wp.element,t=window.wp.blocks,l=window.wp.i18n,a=window.wp.blockEditor;const n=e=>null==e||""==e;var r=window.wp.data,o=window.wp.components,i=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"gutena/form-field","version":"1.0.0","title":"Form field","parent":["gutena/forms"],"category":"gutena","icon":"feedback","description":"Form field","attributes":{"nameAttr":{"type":"string","default":"input_1"},"fieldName":{"type":"string","default":"Name"},"fieldClasses":{"type":"string","default":""},"fieldType":{"type":"string","default":"text"},"isRequired":{"type":"boolean","default":false},"placeholder":{"type":"string","default":""},"defaultValue":{"type":"string","default":""},"autocomplete":{"type":"boolean","default":false},"autoCapitalize":{"type":"boolean","default":false},"textAreaRows":{"type":"number","default":5},"maxlength":{"type":"number","default":""},"minMaxStep":{"type":"object","default":{}},"selectOptions":{"type":"array","default":["Big","Medium","Small"]},"optionsColumns":{"type":"number","default":1},"optionsInline":{"type":"boolean","default":false},"multiSelect":{"type":"boolean","default":false},"errorRequiredMsg":{"type":"string","default":"Field is required"},"errorInvalidInputMsg":{"type":"string","default":"Input is not valid"},"description":{"type":"string","default":""},"settings":{"type":"object","default":{}}},"usesContext":["gutena-forms/formID"],"supports":{"__experimentalSettings":true,"align":["wide","full"],"color":{"background":true,"text":true},"__experimentalBorder":{"color":true,"radius":true,"style":true,"width":true,"__experimentalDefaultControls":{"color":true,"radius":true,"style":true,"width":true}},"spacing":{"margin":true,"padding":true,"blockGap":{"__experimentalDefault":"2em","sides":["horizontal","vertical"]}},"html":false},"textdomain":"gutena-forms","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');(0,t.registerBlockType)(i,{icon:()=>(0,e.createElement)(o.Icon,{icon:()=>(0,e.createElement)("svg",{width:"24",height:"24",viewBox:"0 0 24 24",fill:"none",xmlns:"http://www.w3.org/2000/svg"},(0,e.createElement)("rect",{x:"2",y:"4",width:"8",height:"2",fill:"#0EA489"}),(0,e.createElement)("rect",{x:"2",y:"11",width:"8",height:"2",fill:"#0EA489"}),(0,e.createElement)("rect",{x:"14",y:"4",width:"8",height:"2",fill:"#0EA489"}),(0,e.createElement)("rect",{x:"14",y:"11",width:"8",height:"2",fill:"#0EA489"}),(0,e.createElement)("rect",{x:"2",y:"18",width:"20",height:"2",fill:"#0EA489"}))}),edit:function(t){let{className:i,attributes:s,setAttributes:u,isSelected:m,clientId:c}=t;const{nameAttr:d,fieldName:p,fieldClasses:g,fieldType:f,isRequired:h,placeholder:x,defaultValue:b,autocomplete:_,autoCapitalize:E,textAreaRows:w,maxlength:y,minMaxStep:C,selectOptions:v,optionsColumns:k,optionsInline:S,multiSelect:T,errorRequiredMsg:R,errorInvalidInputMsg:A,description:N,settings:q,style:I}=s,[M,F]=(0,e.useState)(v[0]),B=(0,r.useSelect)((e=>{let t=e(a.store).getAdjacentBlockClientId(c,-1);return n(t)&&(t=e(a.store).getAdjacentBlockClientId(c,1)),t}),[]),P=(0,r.useSelect)((e=>{if(n(B))return null;let t=e(a.store).getBlockAttributes(B);return n(t)||n(t.content)?"":t.content.replace(/(<([^>]+)>)|\*/gi,"").trim()}),[B]),{updateBlockAttributes:O}=(0,r.useDispatch)(a.store),[j,D]=(0,e.useState)(!1);(0,e.useEffect)((()=>{"input_1"!=d&&""!=d||D(!0)}),[]);const V=function(e){let t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];if(!n(e)){if(u({fieldName:e}),j){let t=e.toLowerCase().replace(/ /g,"_");u({nameAttr:t})}t&&!n(B)&&O(B,{content:e})}};(0,e.useEffect)((()=>{let e=!0;return e&&V(P),()=>{e=!1}}),[P]),(0,e.useEffect)((()=>{let e=!0;if(e){let e=`gutena-forms-field ${f}-field ${h?"required-field":""}`;-1!==["radio","checkbox"].indexOf(f)&&(e+=S?" inline-options":" has-"+k+"-col"),u({fieldClasses:e})}return()=>{e=!1}}),[f,h,S,k]);const $=(0,a.useBlockProps)({className:`gutena-forms-${f}-field`});return(0,e.createElement)(e.Fragment,null,(0,e.createElement)(a.InspectorControls,null,(0,e.createElement)(o.PanelBody,{title:"Form Field",initialOpen:!0},(0,e.createElement)(o.PanelRow,null,(0,e.createElement)(o.SelectControl,{label:"Field Type",value:f,options:[{label:"Text",value:"text"},{label:"Number",value:"number"},{label:"TextArea",value:"textarea"},{label:"Email",value:"email"},{label:"Dropdown",value:"select"},{label:"Radio",value:"radio"},{label:"Checkbox",value:"checkbox"}],onChange:e=>u({fieldType:e}),help:(0,l.__)("Select appropriate field type for input","gutena-forms"),__nextHasNoMarginBottom:!0})),-1!==["radio","checkbox"].indexOf(f)&&(0,e.createElement)(e.Fragment,null,(0,e.createElement)(o.ToggleControl,{label:(0,l.__)("Show Inline","gutena-forms"),className:"gf-mt-1",help:S?(0,l.__)("Toggle to make options show in columns","gutena-forms"):(0,l.__)("Toggle to make options show inline","gutena-forms"),checked:S,onChange:e=>u({optionsInline:e})}),!S&&(0,e.createElement)(o.RangeControl,{label:(0,l.__)("Columns","gutena-forms"),value:k,onChange:e=>u({optionsColumns:e}),min:1,max:6,step:1})),-1!==["text","textarea"].indexOf(f)&&(0,e.createElement)(o.RangeControl,{label:(0,l.__)("Maxlength","gutena-forms"),value:y,onChange:e=>u({maxlength:e}),min:0,max:500,step:25}),("number"===f||"range"===f)&&(0,e.createElement)(e.Fragment,null,(0,e.createElement)("h2",{className:"block-editor-block-card__title gf-mt-1 "},(0,l.__)("Value","gutena-forms")),(0,e.createElement)(o.PanelRow,{className:"gf-child-mb-0 gf-mb-24"},(0,e.createElement)(o.TextControl,{label:(0,l.__)("Minimum","gutena-forms"),value:null==C?void 0:C.min,type:"number",onChange:e=>u({minMaxStep:{...C,min:e}})}),(0,e.createElement)(o.TextControl,{label:(0,l.__)("Maximum","gutena-forms"),value:null==C?void 0:C.max,type:"number",onChange:e=>u({minMaxStep:{...C,max:e}})}),(0,e.createElement)(o.TextControl,{label:(0,l.__)("Step","gutena-forms"),value:null==C?void 0:C.step,type:"number",onChange:e=>u({minMaxStep:{...C,step:e}})}))),"textarea"===f&&(0,e.createElement)(o.RangeControl,{label:(0,l.__)("Textarea Rows","gutena-forms"),value:w,onChange:e=>u({textAreaRows:e}),min:2,max:20,step:1}),"select"===f&&(0,e.createElement)(o.FormTokenField,{label:(0,l.__)("Options","gutena-forms"),value:v,suggestions:v,onChange:e=>u({selectOptions:e})}),(0,e.createElement)(o.PanelRow,null,(0,e.createElement)(o.TextControl,{label:(0,l.__)("Field Name","gutena-forms"),value:p,onChange:e=>V(e,!0)})),(0,e.createElement)(o.PanelRow,null,(0,e.createElement)(o.TextControl,{label:(0,l.__)("Field Name Attribute","gutena-forms"),help:(0,l.__)("Contains only letters, numbers, and underscore","gutena-forms"),value:d,onChange:e=>u({nameAttr:e.toLowerCase().replace(/ /g,"_")})})),(0,e.createElement)(o.PanelRow,null,(0,e.createElement)(o.TextControl,{label:(0,l.__)("Placeholder","gutena-forms"),value:x,onChange:e=>u({placeholder:e})})),(0,e.createElement)(o.PanelRow,null,(0,e.createElement)(o.ToggleControl,{label:(0,l.__)("Required","gutena-forms"),help:h?(0,l.__)("Toggle to make input field not required","gutena-forms"):(0,l.__)("Toggle to make input field required","gutena-forms"),checked:h,onChange:e=>u({isRequired:e})})))),(0,e.createElement)("div",$,f.length>0?0<=["text","email","number","hidden","tel","url"].indexOf(f)?(0,e.createElement)("input",{type:f,className:g,placeholder:x||(0,l.__)("Placeholder…"),required:h?"required":""}):"textarea"===f?(0,e.createElement)("textarea",{className:g,placeholder:x||(0,l.__)("Placeholder…"),required:h?"required":"",rows:w}):"select"===f?(0,e.createElement)("select",{className:g,value:M,onChange:e=>F(e.target.value),required:h?"required":""},v.map(((t,l)=>(0,e.createElement)("option",{key:l,value:t},t)))):"radio"===f||"checkbox"===f?(0,e.createElement)("div",{className:g},v.map(((t,l)=>(0,e.createElement)("label",{key:l,className:f+"-container"},t,(0,e.createElement)("input",{type:f,name:p,value:t,checked:t===M,onChange:e=>F(e.target.value)}),(0,e.createElement)("span",{class:"checkmark"}))))):void 0:""))}})}();