import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';

const blockName = metadata.name;

export default function Edit(): JSX.Element {
  const blockProps = useBlockProps({
    className: 'jankx-stock-status',
  });

  return (
    <div {...blockProps}>
      <ServerSideRender
        block={blockName}
      />
    </div>
  );
}

