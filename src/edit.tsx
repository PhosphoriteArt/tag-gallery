import { __ } from "@wordpress/i18n";

import { useBlockProps, InspectorControls } from "@wordpress/block-editor";

import "./editor.scss";
import React from "react";
import { BlockEditProps } from "@wordpress/blocks";
import definition from "./block.json";
import ServerSideRender from "@wordpress/server-side-render";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 */
export const Edit: React.FC<BlockEditProps<{ query: string }>> = ({
	attributes,
	setAttributes,
}) => {
	const onChangeQuery: React.ChangeEventHandler<HTMLInputElement> =
		React.useCallback((e) => {
			console.log({ query: e.target.value });
			setAttributes({ query: e.target.value });
		}, []);

	return (
		<div {...useBlockProps()}>
			<InspectorControls key="setting">
				<div
					style={{
						display: "flex",
						justifyContent: "center",
						marginBottom: "1rem",
					}}
				>
					<fieldset>
						<legend className="blocks-base-control__label">
							{__("Enter Tag", "tikaka-gallery")}
						</legend>
						<input
							type="text"
							onChange={onChangeQuery}
							value={attributes.query}
							placeholder="e.g. torkku"
						/>
					</fieldset>
				</div>
			</InspectorControls>
			<ServerSideRender block={definition.name} attributes={attributes} />
		</div>
	);
};
