import React, { useEffect, useState } from 'react';
import { Drawer, Button, Spin, Input, Form, Select, Switch, DatePicker, Collapse } from 'antd';
import styled from 'styled-components';
import PropTypes from 'prop-types';
import HTTP from '../../../common/helpers/HTTP';
import Utils from '../../../common/helpers/Utils';
import Routes from '../../../common/helpers/Routes';
import FileUploaderFormInput from '../uploader/FileUploaderFormInput';
import SummernoteEditor from '../common/SummernoteEditor';
import moment from 'moment';

const { Option } = Select;
const { TextArea } = Input;
const { Panel } = Collapse;

const StyledDrawer = styled(Drawer)`
    .ant-drawer-content-wrapper {
        width: 760px !important;
        @media (max-width: 768px) {
            max-width: calc(100vw - 16px) !important;
        }
    }
`;

const Post = (props) => {
    const [visible, setVisible] = useState(false);
    const [form] = Form.useForm();
    const [loading, setLoading] = useState((typeof props.loading !== 'undefined') ? props.loading : false);
    const [componentLoading, setComponentLoading] = useState((typeof props.componentLoading !== 'undefined') ? props.componentLoading : false);
    const [bodyValue, setBodyValue] = useState('');
    const [categories, setCategories] = useState([]);
    const [tags, setTags] = useState([]);

    useEffect(() => {
        loadTaxonomies();
    }, []);

    useEffect(() => {
        const initialBody = props.itemToEdit ? props.itemToEdit.body : '';
        form.setFieldsValue({
            id: props.itemToEdit ? props.itemToEdit.id : '',
            title: props.itemToEdit ? props.itemToEdit.title : '',
            slug: props.itemToEdit ? props.itemToEdit.slug : '',
            category_id: props.itemToEdit ? (props.itemToEdit.category ? props.itemToEdit.category.id : props.itemToEdit.category_id) : undefined,
            tags: props.itemToEdit && props.itemToEdit.tags ? props.itemToEdit.tags.map(tag => tag.id) : [],
            status: props.itemToEdit ? props.itemToEdit.status : 'draft',
            allow_comments: props.itemToEdit ? Boolean(props.itemToEdit.allow_comments) : true,
            published_at: props.itemToEdit && props.itemToEdit.published_at ? moment(props.itemToEdit.published_at) : null,
            excerpt: props.itemToEdit ? props.itemToEdit.excerpt : '',
            cover_image: props.itemToEdit ? props.itemToEdit.cover_image : '',
            meta_title: props.itemToEdit ? props.itemToEdit.meta_title : '',
            meta_description: props.itemToEdit ? props.itemToEdit.meta_description : '',
            meta_keywords: props.itemToEdit ? props.itemToEdit.meta_keywords : '',
            canonical_url: props.itemToEdit ? props.itemToEdit.canonical_url : '',
            body: initialBody
        });
        setBodyValue(initialBody);
    }, [props.itemToEdit]);

    useEffect(() => {
        setTimeout(() => {
            setVisible(props.visible);
        }, 100);
    }, [props.visible]);

    useEffect(() => {
        if (typeof props.loading !== 'undefined') {
            setLoading(props.loading);
        }
    }, [props.loading]);

    useEffect(() => {
        if (typeof props.componentLoading !== 'undefined') {
            setComponentLoading(props.componentLoading);
        }
    }, [props.componentLoading]);

    const loadTaxonomies = () => {
        HTTP.get(Routes.api.admin.blogCategories, { params: { all: true } })
        .then(response => {
            Utils.handleSuccessResponse(response, () => {
                setCategories(Array.isArray(response.data.payload) ? response.data.payload : []);
            });
        })
        .catch((error) => {
            Utils.handleException(error);
        });

        HTTP.get(Routes.api.admin.blogTags, { params: { all: true } })
        .then(response => {
            Utils.handleSuccessResponse(response, () => {
                setTags(Array.isArray(response.data.payload) ? response.data.payload : []);
            });
        })
        .catch((error) => {
            Utils.handleException(error);
        });
    };

    const handleClose = () => {
        setVisible(false);
        setTimeout(() => {
            props.handleCancel();
        }, 400);
    };

    const handleOk = () => {
        form
        .validateFields()
        .then((values) => {
            setLoading(true);

            const formData = new FormData();
            values.id && formData.append('_method', 'put');

            values.id && formData.append('id', values.id);
            formData.append('title', values.title);
            values.slug && formData.append('slug', values.slug);
            formData.append('category_id', values.category_id);
            formData.append('status', values.status);
            formData.append('allow_comments', values.allow_comments ? 1 : 0);
            values.excerpt && formData.append('excerpt', values.excerpt);
            values.body && formData.append('body', values.body);

            if (values.published_at) {
                formData.append('published_at', values.published_at.format('YYYY-MM-DD HH:mm:ss'));
            }

            if (values.cover_image) {
                formData.append('cover_image', values.cover_image);
            }

            values.meta_title && formData.append('meta_title', values.meta_title);
            values.meta_description && formData.append('meta_description', values.meta_description);
            values.meta_keywords && formData.append('meta_keywords', values.meta_keywords);
            values.canonical_url && formData.append('canonical_url', values.canonical_url);

            if (values.tags && values.tags.length) {
                values.tags.forEach(tagId => {
                    formData.append('tags[]', tagId);
                });
            }

            HTTP.post(Routes.api.admin.blogPosts + (values.id ? `/${values.id}` : ''), formData)
            .then(response => {
                Utils.handleSuccessResponse(response, () => {
                    form.resetFields();
                    setBodyValue('');
                    Utils.showNotification(response.data.message, 'success');
                    props.submitCallback();
                });
            })
            .catch((error) => {
                Utils.handleException(error);
            }).finally(() => {
                setLoading(false);
            });
        })
        .catch((info) => {
            console.log('Validate Failed:', info);
        });
    };

    const coverOnChange = (files) => {
        form.setFieldsValue({
            cover_image: files.length ? files[0] : ''
        });
    };

    return (
        <StyledDrawer
            title={props.title}
            onClose={handleClose}
            visible={visible}
            destroyOnClose={true}
            maskClosable={false}
            forceRender={true}
            footer={
                <div
                    style={{
                        textAlign: 'right',
                    }}
                >
                    <Button disabled={componentLoading} onClick={handleClose} style={{ marginRight: 8 }}>
                        Cancel
                    </Button>
                    <Button disabled={componentLoading} onClick={handleOk} type="primary" loading={loading}>
                        Save
                    </Button>
                </div>
            }
        >
            <Spin spinning={componentLoading} size="large" delay={500}>
                <Form
                    preserve={false}
                    form={form}
                    layout="vertical"
                    name="blog-post"
                >
                    <Form.Item name="id" hidden>
                        <Input/>
                    </Form.Item>
                    <Form.Item
                        name="title"
                        label="Title"
                        rules={[
                            {
                                required: true,
                                message: 'Please enter post title',
                            },
                        ]}
                    >
                        <Input placeholder="Enter Title"/>
                    </Form.Item>
                    <Form.Item name="slug" label="Slug">
                        <Input placeholder="Auto-generated if empty"/>
                    </Form.Item>
                    <Form.Item
                        name="category_id"
                        label="Category"
                        rules={[
                            {
                                required: true,
                                message: 'Please select a category'
                            },
                        ]}
                    >
                        <Select
                            placeholder="Select Category"
                            allowClear
                        >
                            {categories.map((category) => (
                                <Option key={category.id} value={category.id}>{category.name}</Option>
                            ))}
                        </Select>
                    </Form.Item>
                    <Form.Item
                        name="tags"
                        label="Tags"
                    >
                        <Select
                            mode="multiple"
                            placeholder="Select Tags"
                            allowClear
                        >
                            {tags.map((tag) => (
                                <Option key={tag.id} value={tag.id}>{tag.name}</Option>
                            ))}
                        </Select>
                    </Form.Item>
                    <Form.Item
                        name="status"
                        label="Status"
                        rules={[
                            {
                                required: true,
                                message: 'Please select a status'
                            },
                        ]}
                    >
                        <Select placeholder="Select Status">
                            <Option value="draft">Draft</Option>
                            <Option value="published">Published</Option>
                            <Option value="disabled">Disabled</Option>
                        </Select>
                    </Form.Item>
                    <Form.Item
                        name="published_at"
                        label="Publish Date"
                    >
                        <DatePicker showTime style={{ width: '100%' }} />
                    </Form.Item>
                    <Form.Item
                        name="allow_comments"
                        label="Allow Comments"
                        valuePropName="checked"
                    >
                        <Switch/>
                    </Form.Item>
                    <Form.Item
                        name="excerpt"
                        label="Excerpt"
                    >
                        <TextArea rows={3} placeholder="Short summary"/>
                    </Form.Item>
                    <Form.Item
                        name="cover_image"
                        label="Cover Image"
                        rules={[
                            {
                                required: !props.itemToEdit,
                                message: 'Please upload cover image',
                            },
                        ]}
                    >
                        <FileUploaderFormInput
                            onChangeCallback={coverOnChange}
                            acceptedFileTypes={"image/*"}
                            previewFile={props.itemToEdit && props.itemToEdit.cover_image ? Utils.backend + '/' + props.itemToEdit.cover_image : null}
                        />
                    </Form.Item>
                    <Form.Item
                        name="body"
                        label="Body"
                        rules={[
                            {
                                required: true,
                                message: 'Please enter post content',
                            },
                        ]}
                    >
                        <SummernoteEditor
                            value={bodyValue}
                            placeholder="Write your post..."
                            height={260}
                            onChange={(content) => {
                                setBodyValue(content);
                                form.setFieldsValue({ body: content });
                            }}
                        />
                    </Form.Item>
                    <Collapse defaultActiveKey={[]}>
                        <Panel header="SEO" key="seo">
                            <Form.Item name="meta_title" label="Meta Title">
                                <Input placeholder="Meta title"/>
                            </Form.Item>
                            <Form.Item name="meta_description" label="Meta Description">
                                <TextArea rows={3} placeholder="Meta description"/>
                            </Form.Item>
                            <Form.Item name="meta_keywords" label="Meta Keywords">
                                <Input placeholder="keyword1, keyword2"/>
                            </Form.Item>
                            <Form.Item name="canonical_url" label="Canonical URL">
                                <Input placeholder="https://example.com/blog/post-slug"/>
                            </Form.Item>
                        </Panel>
                    </Collapse>
                </Form>
            </Spin>
        </StyledDrawer>
    );
};

Post.propTypes = {
    handleCancel: PropTypes.func.isRequired,
    submitCallback: PropTypes.func.isRequired,
    visible: PropTypes.bool.isRequired,
    itemToEdit: PropTypes.object,
    loading: PropTypes.bool,
    componentLoading: PropTypes.bool,
    title: PropTypes.node,
};

export default Post;
