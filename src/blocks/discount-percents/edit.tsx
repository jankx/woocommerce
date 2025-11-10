import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import {
  InspectorControls,
  useBlockProps,
} from '@wordpress/block-editor';
import {
    PanelBody,
    TextControl,
    ToggleControl,
    Notice,
} from '@wordpress/components';
import metadata from './block.json';
import type { DiscountPercentsAttributes } from './types';

interface EditProps {
  attributes: DiscountPercentsAttributes;
  setAttributes: (attributes: Partial<DiscountPercentsAttributes>) => void;
  context?: Record<string, unknown>;
}

const blockName = metadata.name;

export default function Edit({
  attributes,
  setAttributes,
}: EditProps): JSX.Element {
  const {
    prefix = '-',
    suffix = '%',
    displayZero = false,
  } = attributes;

  const blockProps = useBlockProps({
    className: 'jankx-discount-percents',
  });

  return (
    <>
      <InspectorControls>
        <PanelBody
          title={__('Display Settings', 'jankx')}
          initialOpen={true}
        >
          <TextControl
            label={__('Prefix', 'jankx')}
            value={prefix}
            help={__('Text placed before the discount value.', 'jankx')}
            onChange={(value: string) => setAttributes({ prefix: value })}
          />
          <TextControl
            label={__('Suffix', 'jankx')}
            value={suffix}
            help={__('Text placed after the discount value.', 'jankx')}
            onChange={(value: string) => setAttributes({ suffix: value })}
          />
          <ToggleControl
            label={__('Display when discount equals zero', 'jankx')}
            checked={displayZero}
            onChange={(value: boolean) => setAttributes({ displayZero: value })}
            help={__(
              'Enable if you want to display the badge even when the product is not discounted.',
              'jankx',
            )}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <Notice
          status="info"
          isDismissible={false}
        >
          {__(
            'The preview reflects live pricing data. If no product context is available, a placeholder value will be shown.',
            'jankx',
          )}
        </Notice>
        <ServerSideRender
          block={blockName}
          attributes={attributes}
        />
      </div>
    </>
  );
}

