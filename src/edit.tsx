import { __ } from "@wordpress/i18n";

import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { ColorPicker } from "@wordpress/components";

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
export const Edit: React.FC<
	BlockEditProps<{
		query: string;
		ascending: boolean;
		gallery_size: number;
		gallery_spacing: number;
		gallery_color: string;
		gallery_border: number;
		gallery_popout: number;
	}>
> = ({ attributes, setAttributes }) => {
	const onChangeQuery: React.ChangeEventHandler<HTMLInputElement> =
		React.useCallback((e) => {
			console.log({ query: e.target.value });
			setAttributes({ query: e.target.value });
		}, []);

	console.log(attributes);

	return (
		<div {...useBlockProps()}>
			<InspectorControls key="setting">
				<style>{`
				.tag-gallery-settings details {
					border: 1px solid #aaa;
					border-radius: 4px;
					padding: 0.5em 0.5em 0;
					margin-bottom: 1rem;
				}
				
				.tag-gallery-settings summary {
					font-weight: bold;
					margin: -0.5em -0.5em 0;
					padding: 0.5em;
				}
				
				.tag-gallery-settings details[open] {
					padding: 0.5em;
				}
				.tag-gallery-settings .blocks-base-control__label {
					display: block;
					margin-bottom: 0.5rem;
				}
				`}</style>
				<div
					style={{
						display: "flex",
						alignItems: "stretch",
						marginBottom: "1rem",
						flexDirection: "column",
						paddingLeft: "1rem",
						paddingRight: "1rem"
					}}
					className="tag-gallery-settings"
				>
					<div
						style={{
							display: "flex",
							alignItems: "stretch",
							flexDirection: "column",
						}}
					>
						<label className="blocks-base-control__label">
							<div>{__("Enter Tag", "tag-gallery")}</div>
							<input
								type="text"
								onChange={onChangeQuery}
								value={attributes.query}
								placeholder="e.g. torkku"
							/>
						</label>

						<label className="blocks-base-control__label">
							<input
								type="checkbox"
								onChange={(e) =>
									setAttributes({ ascending: !e.target.checked })
								}
								checked={!attributes.ascending}
								placeholder="e.g. torkku"
							/>

							<span>{__("Reverse Chronological", "tag-gallery")}</span>
						</label>
						<details>
							<summary>Spacing Settings</summary>
							<label className="blocks-base-control__label">
								<div>{__("Gallery Size (px)", "tag-gallery")}</div>
								<input
									type="number"
									onChange={(e) =>
										setAttributes({
											gallery_size: parseInt(e.target.value || "0"),
										})
									}
									value={attributes.gallery_size}
									placeholder="150"
								/>
							</label>
							<label className="blocks-base-control__label">
								<div>{__("Gallery Spacing (px)", "tag-gallery")}</div>
								<input
									type="number"
									onChange={(e) =>
										setAttributes({
											gallery_spacing: parseInt(e.target.value || "0"),
										})
									}
									value={attributes.gallery_spacing}
									placeholder="150"
								/>
							</label>
							<label className="blocks-base-control__label">
								<div>{__("Gallery Border Size (px)", "tag-gallery")}</div>
								<input
									type="number"
									onChange={(e) =>
										setAttributes({
											gallery_border: parseInt(e.target.value || "0"),
										})
									}
									value={attributes.gallery_border}
									placeholder="5"
								/>
							</label>
							<label className="blocks-base-control__label">
								<div>{__("Gallery Popout Amount (px)", "tag-gallery")}</div>
								<input
									type="number"
									onChange={(e) =>
										setAttributes({
											gallery_popout: parseInt(e.target.value || "0"),
										})
									}
									value={attributes.gallery_popout}
									placeholder="10"
								/>
							</label>
						</details>
						<details>
							<summary>Highlight Color</summary>
							<ColorPicker
								color={attributes.gallery_color}
								onChangeComplete={(value) =>
									setAttributes({ gallery_color: value.hex })
								}
							/>
						</details>
						<details>
							<summary>Advanced Search</summary>
							<div>
								<header style={{fontWeight: 'bold'}}>Did you know you that you can search for multiple tags, or exclude tags?</header>
								<p>
								Specify multiple tags with spaces to combine them: <code>vilu puddle</code>
								</p>
								<p>
									Specify a tag prepended with <code>!</code> to exclude posts tagged with that: <code>vilu !puddle</code> (posts with vilu but without puddle)
								</p>
								<p>
									Use the special tag <code>*</code> to include all: <code>* !torkku</code> (every post except those with torkku)
								</p>
								<p>
									Instructions are carried out left-to-right
								</p>
							</div>
						</details>
					</div>
				</div>
			</InspectorControls>
			<ServerSideRender block={definition.name} attributes={attributes} />
		</div>
	);
};
