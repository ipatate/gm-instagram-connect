import { registerBlockType } from "@wordpress/blocks";
import { withSelect } from "@wordpress/data";
import ServerSideRender from "@wordpress/server-side-render";
import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";

registerBlockType("goodmotion/instagram", {
  title: __("GM Instagram Feed", "gm-instagram-connect"),
  description: __("Block for display instagram feed.", "gm-instagram-connect"),
  icon: "star-filled",
  category: "goodmotion-block",
  example: {},
  attributes: {},
  edit: (props) => {
    const blockProps = useBlockProps();
    return (
      <div {...blockProps}>
        <ServerSideRender
          block="goodmotion/instagram"
          attributes={props.attributes}
        />
      </div>
    );
  },
  // save
});
