import { Button, Col, Empty, Form, Input, List, Row, Space, Typography } from 'antd';
import React, { useEffect, useRef, useState } from 'react';
import styled, { keyframes, css } from 'styled-components';
import HTTP from '../../../common/helpers/HTTP';
import Routes from '../../../common/helpers/Routes';
import Utils from '../../../common/helpers/Utils';
import FileUploader from '../uploader/FileUploader';
import Typed from 'typed.js';
import QueueAnim from 'rc-queue-anim';
import SocialLinkPopup from './SocialLinkPopup';
import { DownloadOutlined } from '@ant-design/icons';
import PageWrapper from '../layout/PageWrapper';
import SummernoteEditor from '../common/SummernoteEditor';

const pulseAnimation = keyframes`
0%,
100% {
-webkit-transform: translateX(0);
        transform: translateX(0);
}
10%,
30%,
50%,
70% {
-webkit-transform: translateX(-10px);
        transform: translateX(-10px);
}
20%,
40%,
60% {
-webkit-transform: translateX(10px);
        transform: translateX(10px);
}
80% {
-webkit-transform: translateX(8px);
        transform: translateX(8px);
}
90% {
-webkit-transform: translateX(-8px);
        transform: translateX(-8px);
}
`;

const AnimatedDiv = styled.div`
animation: ${props => props.animate ? css`${pulseAnimation} 2s ease-in-out infinite;` : ''};
`;

const { Title, Text, Paragraph } = Typography;
const { Item } = List;

const EditSpan = styled.span`
i:hover, svg:hover {
	transform: rotate(180deg);
}
i, svg {
    text-decoration: none;
    width: 20px;
    height: 20px;
    font-size: 10px;
    line-height: 20px;
    text-align: center;
    border-radius: 50%;
    padding: 0;
    border: 0;
    cursor: pointer;
    background-color: #ddd;
    margin-left: 5px;
    opacity: 0.7;
    flex-shrink: 0;
    -webkit-transition: all 0.3s ease;
    -moz-transition: all 0.3s ease;
    -o-transition: all 0.3s ease;
    transition: all 0.3s ease;
}
`;

const About = () => {
    const [avatar, setAvatar] = useState(null);
    const [avatarUrl, setAvatarUrl] = useState(null);
    const [name, setName] = useState('');
    const [jobTitle, setJobTitle] = useState('');
    const [email, setEmail] = useState('');
    const [phone, setPhone] = useState('');
    const [address, setAddress] = useState('');
    const [description, setDescription] = useState('');
    const [heroSubtitle, setHeroSubtitle] = useState('');
    const [cv, setCv] = useState(null);
    const [cvUrl, setCvUrl] = useState(null);
    const [cover, setCover] = useState(null);
    const [coverUrl, setCoverUrl] = useState(null);
    const [taglines, setTagLines] = useState([]);
    const [aboutHighlights, setAboutHighlights] = useState([]);
    const [socialLinks, setSocialLinks] = useState([]);
    
    const [focusTaglines, setFocusTaglines] = useState(false);
    const [focusHighlights, setFocusHighlights] = useState(false);
    const [focusSocialLinks, setFocusSocialLinks] = useState(false);

    const [componentLoading, setComponentLoading] = useState(true);
    const [loading, setLoading]= useState(false);
    const [socialLinkPopupVisible, setSocialLinkPopupVisible] = useState(false);
    const [socialLinkToEdit, setSocialLinkToEdit] = useState(null);

    const [form] = Form.useForm();

    const nameInput = useRef(null);
    const jobTitleInput = useRef(null);
    const emailInput = useRef(null);
    const phoneInput = useRef(null);
    const addressInput = useRef(null);
    const descriptionInput = useRef(null);
    const heroSubtitleInput = useRef(null);
    const taglinesInput = useRef(null);
    const highlightsInput = useRef(null);
    const socialLinksInput = useRef(null);

    const typedElement = useRef(null);
    const typed = useRef(null);

    useEffect(() => {
        loadData();
    }, [])

    useEffect(() => {
        console.log(typedElement);
        const options = {
            strings: taglines && taglines.length ? taglines : [''],
            typeSpeed: 70,
            backSpeed: 40,
            smartBackspace: true,
            loop: true
        };
        
        if (typedElement.current) {
            typed.current = new Typed(typedElement.current, options);
        }
        
        return () => {
            if (typed.current) {
                typed.current.destroy();
            }
        }
    }, [componentLoading, taglines])

    useEffect(() => {
        if (focusTaglines === true) {
            setTimeout(() => {
                setFocusTaglines(false);
            }, 900);
        }
    }, [focusTaglines]);

    useEffect(() => {
        if (focusHighlights === true) {
            setTimeout(() => {
                setFocusHighlights(false);
            }, 900);
        }
    }, [focusHighlights]);

    useEffect(() => {
        if (focusSocialLinks === true) {
            setTimeout(() => {
                setFocusSocialLinks(false);
            }, 900);
        }
    }, [focusSocialLinks]);

    const avatarUploadCallback = (file, payload = null) => {
        setAvatar(file);
        setAvatarUrl(payload && payload.url ? payload.url : (file ? Utils.backend + '/' + file : null));
    }

    const loadData = () => {
        HTTP.get(Routes.api.admin.about)
        .then(response => {
            Utils.handleSuccessResponse(response, () => {
                setAvatar(response.data.payload.avatar);
                setName(response.data.payload.name);
                setJobTitle(response.data.payload.job_title);
                setEmail(response.data.payload.email);
                setPhone(response.data.payload.phone);
                setAddress(response.data.payload.address);
                setDescription(response.data.payload.description);
                setHeroSubtitle(response.data.payload.hero_subtitle);
                setCv(response.data.payload.cv);
                setCvUrl(response.data.payload.cv_url ? response.data.payload.cv_url : (response.data.payload.cv ? Utils.backend + '/' + response.data.payload.cv : null));
                setCover(response.data.payload.cover);
                setCoverUrl(response.data.payload.cover_url ? response.data.payload.cover_url : (response.data.payload.cover ? Utils.backend + '/' + response.data.payload.cover : null));
                setAvatarUrl(response.data.payload.avatar_url ? response.data.payload.avatar_url : (response.data.payload.avatar ? Utils.backend + '/' + response.data.payload.avatar : null));
                setTagLines(response.data.payload.taglines ? JSON.parse(response.data.payload.taglines) : []);
                setAboutHighlights(response.data.payload.about_highlights ? JSON.parse(response.data.payload.about_highlights) : []);
                setSocialLinks(response.data.payload.social_links ? JSON.parse(response.data.payload.social_links) : []);

                //set form values
                form.setFieldsValue({
                    name: response.data.payload.name,
                    job_title: response.data.payload.job_title,
                    email: response.data.payload.email,
                    phone: response.data.payload.phone,
                    address: response.data.payload.address,
                    description: response.data.payload.description,
                    hero_subtitle: response.data.payload.hero_subtitle,
                });
            })
        })
        .catch(error => {
            Utils.handleException(error);
        }).finally(() => {
            setComponentLoading(false);
        });
    }

    const focusInput = (input) => {
        if (input === 'name') {
            nameInput.current.focus();
        } else if (input === 'jobTitle') {
            jobTitleInput.current.focus();
        } else if (input === 'email') {
            emailInput.current.focus();
        } else if (input === 'phone') {
            phoneInput.current.focus();
        } else if (input === 'address') {
            addressInput.current.focus();
        } else if (input === 'description') {
            if (descriptionInput.current) {
                descriptionInput.current.focus();
            }
        } else if (input === 'heroSubtitle') {
            if (heroSubtitleInput.current) {
                heroSubtitleInput.current.focus();
            }
        } else if (input === 'taglines') {
            setFocusTaglines(true);
            taglinesInput.current.focus();
        } else if (input === 'highlights') {
            setFocusHighlights(true);
            highlightsInput.current.focus();
        }  else if (input === 'socialLinks') {
            setFocusSocialLinks(true);
            socialLinksInput.current.focus();
        }
    }

    const handleSubmit = () => {
        form
        .validateFields()
        .then((values) => {
            //save form
            setLoading(true);

            HTTP.post(Routes.api.admin.about, {
                name: values.name,
                job_title: values.job_title,
                email: values.email,
                phone: values.phone,
                address: values.address,
                description: values.description,
                hero_subtitle: values.hero_subtitle,
                taglines: taglines,
                about_highlights: aboutHighlights,
                social_links: socialLinks,
            })
            .then(response => {
                Utils.handleSuccessResponse(response, () => {
                    Utils.showNotification(response.data.message, 'success');
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
    }

    const onFormValuesChange = (changedValues) => {
        if (typeof changedValues.name !== 'undefined') {
            setName(changedValues.name);
        } else if (typeof changedValues.job_title !== 'undefined') {
            setJobTitle(changedValues.job_title);
        } else if (typeof changedValues.email !== 'undefined') {
            setEmail(changedValues.email);
        } else if (typeof changedValues.phone !== 'undefined') {
            setPhone(changedValues.phone);
        } else if (typeof changedValues.address !== 'undefined') {
            setAddress(changedValues.address);
        } else if (typeof changedValues.description !== 'undefined') {
            setDescription(changedValues.description);
        } else if (typeof changedValues.hero_subtitle !== 'undefined') {
            setHeroSubtitle(changedValues.hero_subtitle);
        }
    }

    const taglineNewHandler = () => {
        let array = [...taglines];
        array.push('');
        setTagLines(array);
    }

    const taglineDeleteHandler = (index) => {
        let array = [...taglines];
        if (index !== -1) {
            array.splice(index, 1);
            setTagLines(array);
        }
    }

    const taglineEditHandler = (e, index) => {
        let array = [...taglines];
        array[index] = e.target.value;
        setTagLines(array);
    }

    const highlightNewHandler = () => {
        let array = [...aboutHighlights];
        array.push('');
        setAboutHighlights(array);
    }

    const highlightDeleteHandler = (index) => {
        let array = [...aboutHighlights];
        if (index !== -1) {
            array.splice(index, 1);
            setAboutHighlights(array);
        }
    }

    const highlightEditHandler = (e, index) => {
        let array = [...aboutHighlights];
        array[index] = e.target.value;
        setAboutHighlights(array);
    }

    const socialLinksNewHandler = () => {
        setSocialLinkToEdit(null);
        setSocialLinkPopupVisible(true);
    }

    const socialLinkDeleteHandler = (index) => {
        let array = [...socialLinks];
        if (index !== -1) {
            array.splice(index, 1);
            setSocialLinks(array);
        }
    }

    const cvUploadCallback = (file, payload = null) => {
        setTimeout(() => {
            setCv(file);
            setCvUrl(payload && payload.url ? payload.url : (file ? Utils.backend + '/' + file : null));
        }, 2000);
    }
    
    const coverUploadCallback = (file, payload = null) => {
        setTimeout(() => {
            setCover(file);
            setCoverUrl(payload && payload.url ? payload.url : (file ? Utils.backend + '/' + file : null));
        }, 2000);
    }

    const socialLinkEditHandler = (index) => {
        setSocialLinkToEdit({
            index: index,
            data: socialLinks[index]
        });
        setSocialLinkPopupVisible(true);
    }

    return (
        <React.Fragment>
            <Row gutter={24}>
                <Col 
                    xl={10}
                    lg={10}
                    md={24}
                    sm={24}
                    xs={24}
                    style={{
                        marginBottom: 24,
                    }}
                >
                    <PageWrapper loading={componentLoading}>
                        <List
                            itemLayout="horizontal"
                            size="large"
                        >
                            <Item>
                                <Space direction="vertical" align="center" style={{width: '100%'}}>
                                        <FileUploader
                                            allowRevert={true}
                                            isAvatar={true}
                                            previewAvatar={avatarUrl}
                                        acceptedFileTypes={"image/*"}
                                        allowMultiple={false}
                                        name={'file'}
                                        serverUrl={Routes.api.admin.avatar}
                                        afterUploadCallback={avatarUploadCallback}
                                        afterRevertCallback={avatarUploadCallback}
                                    />
                                    <Title level={4}>
                                        {name}
                                        <EditSpan onClick={() => {focusInput('name')}}>
                                            <i className="fa fa-pencil-alt" title="Edit"></i>
                                        </EditSpan>
                                    </Title>
                                </Space>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        Job Title
                                        <EditSpan onClick={() => {focusInput('jobTitle')}}>
                                            <i className="fa fa-pencil-alt" title="Edit"></i>
                                        </EditSpan>
                                    </React.Fragment>
                                } description={
                                    <Text type="secondary">{jobTitle || 'Not set'}</Text>
                                }/>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        Email
                                        <EditSpan onClick={() => {focusInput('email')}}>
                                            <i className="fa fa-pencil-alt" title="Edit"></i>
                                        </EditSpan>
                                    </React.Fragment>
                                } description={
                                    <Text type="secondary">{email}</Text>
                                }/>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        Phone
                                        <EditSpan onClick={() => {focusInput('phone')}}>
                                            <i className="fa fa-pencil-alt" title="Edit"></i>
                                        </EditSpan>
                                    </React.Fragment>
                                } description={
                                    <Text type="secondary">{phone}</Text>
                                }/>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        Address
                                        <EditSpan onClick={() => {focusInput('address')}}>
                                            <i className="fa fa-pencil-alt" title="Edit"></i>
                                        </EditSpan>
                                    </React.Fragment>
                                } description={
                                    <Text type="secondary">{address}</Text>
                                }/>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        Description
                                        <EditSpan onClick={() => {focusInput('description')}}>
                                            <i className="fa fa-pencil-alt" title="Edit"></i>
                                        </EditSpan>
                                    </React.Fragment>
                                } description={
                                    <Paragraph type="secondary" style={{textAlign: 'justify'}}>{description}</Paragraph>
                                }/>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        Hero Subtitle
                                        <EditSpan onClick={() => {focusInput('heroSubtitle')}}>
                                            <i className="fa fa-pencil-alt" title="Edit"></i>
                                        </EditSpan>
                                    </React.Fragment>
                                } description={
                                    <Paragraph type="secondary" style={{textAlign: 'justify'}}>{heroSubtitle || 'Not set'}</Paragraph>
                                }/>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        Taglines
                                        <EditSpan onClick={() => {focusInput('taglines')}}>
                                            <i className="fa fa-pencil-alt" title="Edit"></i>
                                        </EditSpan>
                                    </React.Fragment>
                                } description={
                                    <div>
                                        <span ref={typedElement}></span>
                                    </div>
                                }/>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        Highlights
                                        <EditSpan onClick={() => {focusInput('highlights')}}>
                                            <i className="fa fa-pencil-alt" title="Edit"></i>
                                        </EditSpan>
                                    </React.Fragment>
                                } description={
                                    aboutHighlights.length ? (
                                        <Space wrap>
                                            {aboutHighlights.map((highlight, index) => (
                                                <Text type="secondary" key={index}>{highlight}</Text>
                                            ))}
                                        </Space>
                                    ) : (
                                        <Text type="secondary">No highlights set</Text>
                                    )
                                }/>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        Social Links
                                        <EditSpan onClick={() => {focusInput('socialLinks')}}>
                                            <i className="fa fa-pencil-alt" title="Edit"></i>
                                        </EditSpan>
                                    </React.Fragment>
                                } description={
                                    <Space wrap>
                                        {
                                            socialLinks.map((socialLink, index) => (
                                                <a key={index} href={socialLink.link} target="_blank" rel="noreferrer"><i className={socialLink.iconClass}></i></a>
                                            ))
                                        }
                                    </Space>
                                }/>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        CV
                                    </React.Fragment>
                                } description={
                                    <FileUploader
                                        allowRevert={false}
                                        previewFile={cvUrl}
                                        acceptedFileTypes={['text/plain', 'application/pdf', 'application/doc', 'application/rtf']}
                                        allowMultiple={false}
                                        name={'file'}
                                        serverUrl={Routes.api.admin.cv}
                                        labelIdle={'Drag & Drop your CV or <span class="filepond--label-action">Browse</span>'}
                                        afterUploadCallback={cvUploadCallback}
                                        afterRevertCallback={cvUploadCallback}
                                    />
                                }/>
                            </Item>
                            <Item>
                                <Item.Meta title={
                                    <React.Fragment>
                                        Cover Photo
                                    </React.Fragment>
                                } description={
                                    <FileUploader
                                        allowRevert={false}
                                        previewFile={coverUrl}
                                        acceptedFileTypes={"image/*"}
                                        allowMultiple={false}
                                        name={'file'}
                                        serverUrl={Routes.api.admin.cover}
                                        labelIdle={'Drag & Drop your cover or <span class="filepond--label-action">Browse</span>'}
                                        afterUploadCallback={coverUploadCallback}
                                        afterRevertCallback={coverUploadCallback}
                                        imagePreviewMaxHeight={195}
                                    />
                                }/>
                            </Item>
                        </List>
                    </PageWrapper>
                </Col>
                <Col
                    xl={14}
                    lg={14}
                    md={24}
                    sm={24}
                    xs={24}
                    style={{
                        marginBottom: 24,
                    }}
                >
                    <PageWrapper loading={componentLoading}>
                        <Form
                            preserve={false}
                            form={form}
                            onValuesChange={onFormValuesChange}
                            onFinish={handleSubmit}
                            layout="vertical"
                            name="about"
                            requiredMark
                        >
                            <Form.Item
                                name="name"
                                label={<Text strong>Full Name</Text>}
                                messageVariables={{ label: 'Name' }}
                                rules={[
                                    {
                                        required: true,
                                        message: 'Please enter your name'
                                    },
                                ]}
                            >
                                <Input ref={nameInput} placeholder="Full Name"/>
                            </Form.Item>
                            <Form.Item
                                name="job_title"
                                label={<Text strong>Job Title</Text>}
                                messageVariables={{ label: 'Job Title' }}
                            >
                                <Input ref={jobTitleInput} placeholder="Software Engineer"/>
                            </Form.Item>
                            <Form.Item
                                name="email"
                                label={<Text strong>Email</Text>}
                                messageVariables={{ label: 'Email' }}
                                rules={[
                                    {
                                        required: true,
                                        message: 'Please enter your email'
                                    },
                                    {
                                        type: 'email',
                                        message: 'Invalid email address'
                                    }
                                ]}
                            >
                                <Input ref={emailInput} placeholder="Email"/>
                            </Form.Item>
                            <Form.Item
                                name="phone"
                                label={<Text strong>Phone</Text>}
                                messageVariables={{ label: 'Phone' }}
                            >
                                <Input ref={phoneInput} placeholder="Phone"/>
                            </Form.Item>
                            <Form.Item
                                name="address"
                                label={<Text strong>Address</Text>}
                                messageVariables={{ label: 'Address' }}
                            >
                                <Input ref={addressInput} placeholder="Address"/>
                            </Form.Item>
                            <Form.Item
                                name="description"
                                label={<Text strong>Description</Text>}
                                messageVariables={{ label: 'Description' }}
                                rules={[
                                    {
                                        required: true,
                                        message: 'Please enter your description'
                                    }
                                ]}
                            >
                                <SummernoteEditor
                                    ref={descriptionInput}
                                    value={description}
                                    placeholder="Description"
                                    height={220}
                                    onChange={(content) => {
                                        setDescription(content);
                                        form.setFieldsValue({ description: content });
                                    }}
                                />
                            </Form.Item>
                            <Form.Item
                                name="hero_subtitle"
                                label={<Text strong>Hero Subtitle</Text>}
                                messageVariables={{ label: 'Hero Subtitle' }}
                            >
                                <Input.TextArea ref={heroSubtitleInput} placeholder="Short intro for the hero section" rows={3}/>
                            </Form.Item>
                            <AnimatedDiv animate={focusTaglines} ref={taglinesInput} tabIndex="-1">
                                <Form.Item
                                    label={<Text strong>
                                            Taglines
                                            <EditSpan onClick={taglineNewHandler}>
                                                <i className="fa fa-plus" title="Edit"></i>
                                            </EditSpan>
                                        </Text>
                                    }
                                >
                                    <List
                                        size="small"
                                        bordered
                                    >
                                        {
                                            taglines.length ? (
                                                <QueueAnim type={['right', 'left']} leaveReverse>
                                                    {
                                                        taglines.map((item, index) => (
                                                            <div key={index}>
                                                                <List.Item actions={
                                                                    [
                                                                        <EditSpan key={'delete'}>
                                                                            <i className="fas fa-times" style={{color: 'red'}} onClick={()=> taglineDeleteHandler(index)}></i>
                                                                        </EditSpan>
                                                                    ]
                                                                }>
                                                                    <Item.Meta description={
                                                                        <Input placeholder="Enter tag line" value={item} bordered={false} onChange={(e) => taglineEditHandler(e, index)}/>
                                                                    }/>
                                                                </List.Item>
                                                            </div>
                                                        ))
                                                    }
                                                </QueueAnim>
                                            ) : (
                                                <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} />
                                            )
                                        }
                                    </List>
                                </Form.Item>
                            </AnimatedDiv>
                            <AnimatedDiv animate={focusHighlights} ref={highlightsInput} tabIndex="-1">
                                <Form.Item
                                    label={<Text strong>
                                            Highlights
                                            <EditSpan onClick={highlightNewHandler}>
                                                <i className="fa fa-plus" title="Edit"></i>
                                            </EditSpan>
                                        </Text>
                                    }
                                >
                                    <List
                                        size="small"
                                        bordered
                                    >
                                        {
                                            aboutHighlights.length ? (
                                                <QueueAnim type={['right', 'left']} leaveReverse>
                                                    {
                                                        aboutHighlights.map((item, index) => (
                                                            <div key={index}>
                                                                <List.Item actions={
                                                                    [
                                                                        <EditSpan key={'delete'}>
                                                                            <i className="fas fa-times" style={{color: 'red'}} onClick={()=> highlightDeleteHandler(index)}></i>
                                                                        </EditSpan>
                                                                    ]
                                                                }>
                                                                    <Item.Meta description={
                                                                        <Input placeholder="Enter highlight" value={item} bordered={false} onChange={(e) => highlightEditHandler(e, index)}/>
                                                                    }/>
                                                                </List.Item>
                                                            </div>
                                                        ))
                                                    }
                                                </QueueAnim>
                                            ) : (
                                                <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} />
                                            )
                                        }
                                    </List>
                                </Form.Item>
                            </AnimatedDiv>
                            <AnimatedDiv animate={focusSocialLinks} ref={socialLinksInput} tabIndex="-1">
                                <Form.Item
                                    label={<Text strong>
                                            Social Links
                                            <EditSpan onClick={socialLinksNewHandler}>
                                                <i className="fa fa-plus" title="Edit"></i>
                                            </EditSpan>
                                        </Text>
                                    }
                                >
                                    <List
                                        size="small"
                                        bordered
                                    >
                                        {
                                            socialLinks.length ? (
                                                <QueueAnim type={['right', 'left']} leaveReverse>
                                                    {
                                                        socialLinks.map((item, index) => (
                                                            <div key={index}>
                                                                <List.Item actions={
                                                                    [
                                                                        <EditSpan key={'edit'}>
                                                                            <i className="fas fa-pen-square" onClick={()=> socialLinkEditHandler(index)}></i>
                                                                        </EditSpan>,
                                                                        <EditSpan key={'delete'}>
                                                                            <i className="fas fa-times" style={{color: 'red'}} onClick={()=> socialLinkDeleteHandler(index)}></i>
                                                                        </EditSpan>,
                                                                    ]
                                                                }>
                                                                    <Item.Meta description={
                                                                        <a href={item.link} target="_blank" rel="noreferrer">
                                                                            <Space><i className={item.iconClass}></i> {item.title}</Space>
                                                                        </a>
                                                                    }/>
                                                                </List.Item>
                                                            </div>
                                                        ))
                                                    }
                                                </QueueAnim>
                                            ) : (
                                                <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} />
                                            )
                                        }
                                    </List>
                                </Form.Item>
                            </AnimatedDiv>
                            <Form.Item
                                label={<Text strong>
                                        CV
                                    </Text>
                                }
                            >
                                {
                                    cv ? (
                                        <a href={cvUrl} download target="_blank" rel="noreferrer">
                                            <Button type="default" icon={<DownloadOutlined />}>
                                                Download
                                            </Button>
                                        </a>
                                    ) : ''
                                }
                            </Form.Item>
                            <Form.Item
                                style={{
                                    textAlign: 'center',
                                }}
                            >
                                <Button type="primary" htmlType="submit"  loading={loading}>
                                    Save Changes
                                </Button>
                            </Form.Item>
                        </Form>
                    </PageWrapper>
                </Col>
            </Row>
            {
                socialLinkPopupVisible && (
                    <SocialLinkPopup
                        title={'Social Link'}
                        socialLink={socialLinkToEdit}
                        visible={socialLinkPopupVisible}
                        handleCancel={
                            () => {
                                setSocialLinkPopupVisible(false);
                            }
                        }
                        submitCallback={
                            (newSocialLink) => {
                                let newObject = {
                                    title: newSocialLink.title,
                                    link: newSocialLink.link,
                                    iconClass: newSocialLink.iconClass
                                }

                                if (typeof newSocialLink.index !== 'undefined' && newSocialLink.index !== null) {
                                    socialLinks[newSocialLink.index] = newObject;
                                } else {
                                    let array = [...socialLinks];
                                    array.push(newObject);
                                    setSocialLinks(array);
                                }
                                setSocialLinkPopupVisible(false);
                            }
                        }
                    />
                )
            }
        </React.Fragment>
    )
}

export default About;
